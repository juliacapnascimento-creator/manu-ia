<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ChatAssistant
{
    /** @param  array<int, array{role: string, content: string}>  $history */
    public function reply(array $history, string $channel = 'site'): array
    {
        $apiKey = config('services.openai.key');

        if (! is_string($apiKey) || $apiKey === '') {
            return [
                'content' => $this->demoReply($history, $channel),
                'mode' => 'demo',
            ];
        }

        $catalog = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'species', 'category', 'description', 'price', 'availability', 'is_demo'])
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'species' => $product->species,
                'category' => $product->category,
                'description' => $product->description,
                'price' => $product->price,
                'availability' => $product->availability,
                'needs_confirmation' => $product->is_demo,
            ])
            ->all();

        $channelGuidance = match ($channel) {
            'whatsapp' => 'Simule o tom de WhatsApp: caloroso e natural, em mensagens curtas. Não diga que enviou nada de verdade.',
            'instagram' => 'Simule o tom de Direct do Instagram: leve, visual e breve, com no máximo um emoji quando combinar. Não diga que enviou nada de verdade.',
            default => 'Use o tom claro e acolhedor do chat do site.',
        };

        $instructions = implode("\n", [
            'Você é Manu, assistente virtual de atendimento da ManuPekena, uma marca de peças artesanais de biscuit inspiradas na fauna brasileira.',
            'Converse em português brasileiro, com gentileza e naturalidade. Varie as respostas e não repita a mesma frase padrão.',
            'Leve em conta o histórico inteiro: responda a pergunta atual, entenda referências como “ela”, “esse” e “quanto?” pela conversa anterior e não recomece a conversa a cada mensagem.',
            'Se a pessoa responder “sim” a uma pergunta que ofereceu duas opções, peça que ela escolha uma delas em uma frase curta, considerando o assunto já mencionado.',
            $channelGuidance,
            'Use exclusivamente o catálogo abaixo para afirmar nomes, características, preços e disponibilidade. Nunca invente preços, produtos, prazos, políticas, promoções ou possibilidades de personalização.',
            'needs_confirmation=true significa que os dados vieram de uma captura fornecida e ainda precisam de confirmação de preço e disponibilidade pela equipe. Não diga que esses dados são fictícios nem que foram revisados.',
            'Quando a informação não estiver no catálogo, diga claramente que precisa ser consultada com a equipe da ManuPekena.',
            'Ajude a avançar a conversa com uma pergunta curta e específica, sem empilhar perguntas. Se fizer sentido, indique o simulador de orçamento do site.',
            'Não confirme pedidos, pagamentos, compras, disponibilidade final nem mensagens em canais reais. Este site é um protótipo.',
            'Não solicite dados pessoais ou sensíveis. Se a pessoa compartilhar algum, não o repita na resposta.',
            'Catálogo: '.json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-6-luna'),
                'instructions' => $instructions,
                'input' => array_map(fn (array $message): array => [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ], $history),
                'store' => false,
                'max_output_tokens' => 350,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('A API de atendimento está temporariamente indisponível.');
        }

        $answer = collect($response->json('output', []))
            ->where('type', 'message')
            ->flatMap(fn (array $item): array => $item['content'] ?? [])
            ->where('type', 'output_text')
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if ($answer === '') {
            throw new RuntimeException('A resposta da API veio vazia. Tente novamente.');
        }

        return ['content' => $answer, 'mode' => 'openai'];
    }

    /** @param  array<int, array{role: string, content: string}>  $history */
    private function demoReply(array $history, string $channel): string
    {
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();
        $latestMessage = collect($history)->reverse()->firstWhere('role', 'user')['content'] ?? '';
        $normalizedMessage = $this->normalize($latestMessage);
        $matchedProduct = $this->findProduct($products, $latestMessage);
        $questionAboutPrice = preg_match('/(?:\br\$\s*\d|\b(preco|valor|custa|quanto custa|quanto fica)\b)/u', $normalizedMessage) === 1;
        $questionAboutMeasurements = preg_match('/\b(tamanho|mede|medida|medidas|peso|pesa|gramas|centimetros|cm)\b/u', $normalizedMessage) === 1;
        $questionAboutCustomization = preg_match('/\b(personaliz|customiz|cor|tamanho|nome|encomenda)\w*/u', $normalizedMessage) === 1;
        $questionAboutDelivery = preg_match('/\b(prazo|entrega|chega|envio|frete)\w*/u', $normalizedMessage) === 1;
        $questionAboutAvailability = preg_match('/\b(disponivel|tem|estoque|pronta|pronta entrega)\w*/u', $normalizedMessage) === 1;
        $questionAboutPurchase = preg_match('/\b(comprar|compra|pedido|pedir|pagamento|pagar|finalizar)\w*/u', $normalizedMessage) === 1;
        $questionAboutBudget = preg_match('/\b(orcamento|simular|simulacao|quantidade|unidades)\w*/u', $normalizedMessage) === 1;
        $questionAboutCatalog = preg_match('/\b(quais|mostra|mostrar|catalogo|pecas|produtos|opcoes|colecao|animais)\w*/u', $normalizedMessage) === 1;
        $questionAboutRecommendation = preg_match('/\b(indica|recomend|sugere|presente|decorar|colecion)\w*/u', $normalizedMessage) === 1;
        $isGreeting = preg_match('/^(oi|ola|bom dia|boa tarde|boa noite|tudo bem)\b/u', trim($normalizedMessage)) === 1;
        $isAffirmative = preg_match('/^(sim|s|claro|quero|pode|pode ser|isso|vamos|bora|com certeza)$/u', trim(preg_replace('/[^\pL\pN ]/u', '', $normalizedMessage))) === 1;

        if (! $matchedProduct && (
            $this->isFollowUp($normalizedMessage)
            || $isAffirmative
            || $questionAboutPrice
            || $questionAboutMeasurements
            || $questionAboutCustomization
            || $questionAboutDelivery
            || $questionAboutAvailability
            || $questionAboutBudget
            || $questionAboutPurchase
        )) {
            $matchedProduct = $this->findProductInPriorUserMessages($products, $history, $latestMessage);
        }

        if ($isAffirmative) {
            return $this->affirmativeReply($matchedProduct, $history, $channel);
        }

        if ($matchedProduct && $questionAboutPrice) {
            return $this->priceReply($matchedProduct, $channel);
        }

        if ($matchedProduct && $questionAboutMeasurements) {
            preg_match('/\d+[-–]\d+\s*cm(?:\s+e\s+\d+[-–]\d+\s*g)?/u', $matchedProduct->description, $measurements);
            $measurement = $measurements[0] ?? null;

            if ($measurement) {
                return $this->fresh([
                    "A ficha da captura indica {$measurement} para o {$matchedProduct->name}. Vale confirmar essa medida com a equipe junto com os dados atualizados.",
                    "Para o {$matchedProduct->name}, o material que recebi informa {$measurement}. Quer saber mais alguma coisa sobre essa peça?",
                ], $history);
            }

            return "Não encontrei medidas confirmadas para o {$matchedProduct->name}. A equipe pode verificar esse detalhe para você.";
        }

        if ($matchedProduct && $questionAboutCustomization) {
            $personalization = $matchedProduct->personalization_price === null
                ? 'O catálogo não informa o valor nem as opções de personalização para essa peça; a equipe precisa confirmar.'
                : 'No cadastro, o adicional de personalização aparece como R$ '.number_format((float) $matchedProduct->personalization_price, 2, ',', '.').' por unidade.';

            return $this->fresh([
                "A foto mostra o {$matchedProduct->name}, feito em biscuit. {$personalization} Você imaginou alguma mudança específica?",
                "Sobre a personalização do {$matchedProduct->name}: {$personalization} Para não te prometer algo sem confirmação, o ideal é consultar a equipe com a ideia em mãos.",
            ], $history);
        }

        if ($matchedProduct && $questionAboutAvailability) {
            $availability = match ($matchedProduct->availability) {
                'available' => 'O cadastro recebido mostra a peça como disponível.',
                'made_to_order' => 'O cadastro recebido indica que a peça é feita por encomenda.',
                'out_of_stock' => 'O cadastro recebido marca essa peça como indisponível no momento.',
                default => 'O catálogo ainda não traz uma disponibilidade clara para essa peça.',
            };

            return $this->fresh([
                "Para o {$matchedProduct->name}, {$availability} Como esse dado pode mudar, confirme com a equipe antes de contar com ela.",
                "Achei o {$matchedProduct->name} no catálogo. {$availability} Quer que eu te mostre o valor de referência também?",
            ], $history);
        }

        if ($matchedProduct && $questionAboutDelivery) {
            return $this->fresh([
                "Não tenho um prazo de entrega confirmado para o {$matchedProduct->name}. Posso te contar o que aparece no catálogo, mas a equipe precisa confirmar produção e envio.",
                'O material enviado não informa prazo ou frete. A equipe da ManuPekena precisa confirmar isso para a peça que você escolheu.',
            ], $history);
        }

        if (! $matchedProduct && $questionAboutPrice) {
            return $this->fresh([
                'Não encontrei uma peça específica para consultar o preço. Qual destas chamou sua atenção? Posso olhar o valor de referência que aparece na imagem.',
                'Consigo te passar o valor de uma peça do catálogo, mas não quero chutar. Qual bichinho você está pensando?',
            ], $history);
        }

        if (! $matchedProduct && $questionAboutCustomization) {
            return $this->fresh([
                'As imagens não informam valores ou opções de personalização. Me diz qual peça você tem em mente e eu te digo o que está cadastrado; a equipe confirma o que consegue fazer.',
                'Para personalização, preciso saber qual peça você gostou. As opções e os valores devem ser confirmados diretamente com a equipe.',
            ], $history);
        }

        if (! $matchedProduct && $questionAboutAvailability && ! $questionAboutCatalog) {
            return $this->fresh([
                'Não tenho uma disponibilidade atual confirmada. Qual peça você quer consultar? Posso localizar o cadastro e te explicar o que precisa ser confirmado.',
                'Disponibilidade muda, então prefiro não te prometer sem consultar a equipe. Qual dos bichinhos você está procurando?',
            ], $history);
        }

        if ($matchedProduct && ($questionAboutBudget || $questionAboutPurchase)) {
            return "Se você está pensando no {$matchedProduct->name}, use o simulador acima para escolher a quantidade e ver o valor de referência. A simulação fica pendente e não conclui a compra; a equipe confirma preço e disponibilidade atualizados.";
        }

        if ($matchedProduct) {
            $species = $matchedProduct->species ? " ({$matchedProduct->species})" : '';
            $description = $this->shortDescription($matchedProduct->description);
            $channelInvitation = match ($channel) {
                'whatsapp' => 'Quer saber o valor ou pensar em um orçamento? 😊',
                'instagram' => 'Quer ver o valor ou montar uma ideia de orçamento? ✨',
                default => 'Quer saber o valor ou simular uma quantidade?',
            };

            return $this->fresh([
                "Esse é o {$matchedProduct->name}{$species}. {$description} {$channelInvitation}",
                "Boa escolha! O {$matchedProduct->name}{$species} aparece nas fotos que a ManuPekena enviou. {$description} {$channelInvitation}",
                "Encontrei o {$matchedProduct->name} no catálogo. {$description} {$channelInvitation}",
            ], $history);
        }

        if ($questionAboutBudget || $questionAboutPurchase) {
            return $this->fresh([
                'Posso te ajudar a montar a simulação: escolha uma peça, informe a quantidade e veja o cálculo no formulário “Simular orçamento” logo acima. Ele registra uma cotação pendente; não finaliza a compra.',
                'A compra não é concluída pelo chat. Para ter uma referência, use o simulador de orçamento acima: selecione a peça e a quantidade, e depois confirme o valor e o prazo com a equipe.',
            ], $history);
        }

        if ($questionAboutRecommendation) {
            return $this->recommendationReply($products, $normalizedMessage, $channel, $history);
        }

        if ($questionAboutCatalog) {
            return $this->catalogReply($products, $channel, $history);
        }

        if ($questionAboutDelivery) {
            return $this->fresh([
                'Ainda não tenho prazos de produção, entrega ou frete confirmados. Se você me disser qual peça chamou sua atenção, posso verificar o que está cadastrado e indicar o que a equipe precisa confirmar.',
                'Prazo e frete variam e não aparecem no material que recebi. Qual das peças você está pensando em levar? Assim eu te digo o que já consta no catálogo.',
            ], $history);
        }

        if ($isGreeting) {
            return $this->fresh(match ($channel) {
                'whatsapp' => ['Oi! Que bom te ver por aqui 😊 Você quer conhecer alguma peça ou pensar em um presente?', 'Oii! 🌿 Posso te mostrar as peças das fotos ou ajudar com uma simulação. O que você está procurando?'],
                'instagram' => ['Oi! Bem-vinda(o) 🌿 Alguma peça das fotos chamou sua atenção?', 'Oii! ✨ Quer conhecer os bichinhos de biscuit ou já está pensando em um presente?'],
                default => ['Oi! Que bom ter você por aqui. Alguma peça chamou sua atenção ou você quer ver o catálogo primeiro?', 'Olá! Posso te apresentar as peças ou ajudar a montar uma simulação. O que seria mais útil agora?'],
            }, $history);
        }

        if (preg_match('/\b(quem|como|material|feito|biscuit|origem|certificado)\w*/u', $normalizedMessage) === 1) {
            return $this->fresh([
                'As fotos mostram as peças de biscuit e indicam um certificado de origem assinado. Não tenho outros detalhes confirmados sobre o processo; a equipe pode te contar mais.',
                'Pelo material enviado, são peças de biscuit e cada uma aparece acompanhada por um certificado de origem assinado. Quer conhecer uma delas em particular?',
            ], $history);
        }

        return $this->fresh(match ($channel) {
            'whatsapp' => ['Me conta só mais um pouquinho 😊 Você está procurando uma peça específica, um presente ou quer saber o valor?', 'Posso te ajudar com o catálogo, valores de referência ou uma simulação. O que você tem em mente?'],
            'instagram' => ['Posso te mostrar as peças ou ajudar com valores e orçamento ✨ Qual delas você curtiu?', 'Me diz o que você procura: uma peça para presentear, decorar ou colecionar? 🌿'],
            default => ['Posso te ajudar com uma peça, os valores que aparecem no material ou uma simulação de orçamento. O que você gostaria de descobrir?', 'Você está pensando em alguma espécie ou ocasião? Assim eu consigo te indicar algo do catálogo.'],
        }, $history);
    }

    private function catalogReply(Collection $products, string $channel, array $history): string
    {
        if ($products->isEmpty()) {
            return 'Ainda não há peças ativas no catálogo. A equipe precisa cadastrar os produtos para eu poder apresentá-los.';
        }

        $items = $products->map(fn (Product $product): string => "• {$product->name}".($product->species ? " ({$product->species})" : ''))->implode("\n");
        $question = match ($channel) {
            'whatsapp' => 'Qual delas você quer conhecer melhor? 😊',
            'instagram' => 'Alguma já virou sua favorita? ✨',
            default => 'Qual delas chamou sua atenção?',
        };

        return $this->fresh([
            "Separei as peças das fotos que recebi:\n{$items}\n\n{$question}",
            "Tem essas opções no catálogo por enquanto:\n{$items}\n\n{$question}",
        ], $history);
    }

    /** @param  array<int, array{role: string, content: string}>  $history */
    private function affirmativeReply(?Product $product, array $history, string $channel): string
    {
        $lastAssistantMessage = collect($history)->reverse()->firstWhere('role', 'assistant')['content'] ?? '';
        $normalizedAssistantMessage = $this->normalize($lastAssistantMessage);
        $offeredTwoOptions = preg_match('/\b(valor|preco)\b.*\b(simular|quantidade|orcamento)\b/u', $normalizedAssistantMessage) === 1;

        if ($product && $offeredTwoOptions) {
            $answer = match ($channel) {
                'whatsapp' => "Claro! Sobre o {$product->name}, você prefere ver o valor de referência ou simular uma quantidade? 😊",
                'instagram' => "Claro! Do {$product->name}, você quer saber o valor ou simular algumas unidades? ✨",
                default => "Claro! Para o {$product->name}, você prefere consultar o valor de referência ou simular uma quantidade?",
            };

            return $answer;
        }

        if ($product) {
            return match ($channel) {
                'whatsapp' => "Combinado 😊 Sobre o {$product->name}, você quer saber o valor, o tamanho ou a disponibilidade?",
                'instagram' => "Claro! ✨ Sobre o {$product->name}, quer ver o valor, o tamanho ou a disponibilidade?",
                default => "Claro. Sobre o {$product->name}, você quer saber o valor, o tamanho ou a disponibilidade?",
            };
        }

        return match ($channel) {
            'whatsapp' => 'Claro 😊 Você prefere conhecer as peças, ver um valor de referência ou montar uma simulação?',
            'instagram' => 'Claro! ✨ Você quer ver as peças, saber um valor ou montar uma simulação?',
            default => 'Claro. Você prefere ver o catálogo, consultar um valor de referência ou simular um orçamento?',
        };
    }

    private function recommendationReply(Collection $products, string $message, string $channel, array $history): string
    {
        $recommendation = $products->first();

        if (preg_match('/\b(presente|presentear|crian|infantil)\w*/u', $message) === 1) {
            $recommendation = $products->first(fn (Product $product): bool => in_array($product->illustration, ['sapinho-montanha', 'tartaruga-verde', 'tamandua-mirim'], true)) ?? $recommendation;
        } elseif (preg_match('/\b(mar|praia|agua|oceano|aquatico)\w*/u', $message) === 1) {
            $recommendation = $products->first(fn (Product $product): bool => in_array($product->illustration, ['boto-cinza', 'tartaruga-verde'], true)) ?? $recommendation;
        } elseif (preg_match('/\b(casa|decor|mesa|estante)\w*/u', $message) === 1) {
            $recommendation = $products->first(fn (Product $product): bool => $product->illustration === 'caranguejo-uca') ?? $recommendation;
        }

        if (! $recommendation) {
            return $this->catalogReply($products, $channel, $history);
        }

        $tail = $channel === 'instagram' ? 'Quer que eu te mostre mais detalhes? ✨' : 'Se quiser, te conto o valor que aparece no material ou ajudo a simular.';

        return $this->fresh([
            "Pelo que você contou, eu começaria pelo {$recommendation->name} ({$recommendation->species}). É uma sugestão baseada no tema que você mencionou, não em avaliações de clientes. {$tail}",
            "Uma opção que combina com o que você procura é o {$recommendation->name} ({$recommendation->species}). {$tail}",
        ], $history);
    }

    private function priceReply(Product $product, string $channel): string
    {
        if ($product->price === null) {
            return "Ainda não há preço cadastrado para o {$product->name}. A equipe da ManuPekena precisa consultar esse valor.";
        }

        $price = 'R$ '.number_format((float) $product->price, 2, ',', '.');
        $notice = $product->is_demo ? ' Esse valor aparece na captura enviada e precisa ser confirmado como atual.' : '';
        $invitation = match ($channel) {
            'whatsapp' => 'Se quiser, me diga a quantidade que você imaginou e a gente monta uma simulação. 😊',
            'instagram' => 'Quer calcular algumas unidades? ✨',
            default => 'Quer simular esse valor para uma quantidade?',
        };

        return "Na imagem enviada, o {$product->name} aparece por {$price} a peça avulsa.{$notice} {$invitation}";
    }

    private function findProduct(Collection $products, string $message): ?Product
    {
        $normalizedMessage = $this->normalize($message);

        return $products->first(function (Product $product) use ($normalizedMessage): bool {
            $terms = [$product->name, $product->species, $product->slug];

            foreach ([$product->name, $product->species, $product->slug] as $term) {
                if ($term) {
                    $normalizedTerm = $this->normalize($term);
                    $terms = [...$terms, ...preg_split('/[\s-]+/u', $normalizedTerm)];
                }
            }

            foreach (array_unique(array_filter($terms)) as $term) {
                $normalizedTerm = $this->normalize($term);

                if (mb_strlen($normalizedTerm) >= 4 && preg_match('/(?<![\pL\pN])'.preg_quote($normalizedTerm, '/').'(?=$|[^\pL\pN])/u', $normalizedMessage) === 1) {
                    return true;
                }
            }

            return false;
        });
    }

    private function findProductInPriorUserMessages(Collection $products, array $history, string $latestMessage): ?Product
    {
        foreach (collect($history)->reverse() as $message) {
            if ($message['role'] !== 'user' || $message['content'] === $latestMessage) {
                continue;
            }

            $product = $this->findProduct($products, $message['content']);

            if ($product) {
                return $product;
            }
        }

        return null;
    }

    private function isFollowUp(string $message): bool
    {
        return preg_match('/\b(ela|ele|esse|essa|dela|dele|dessa|desse|mais detalhes|me conta mais|quanto|tamanho|medida|peso|e o valor|e o preco|e esse|e essa)\b/u', $message) === 1;
    }

    private function shortDescription(string $description): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $description, 2);

        return trim((string) ($sentences[0] ?? $description));
    }

    /** @param  array<int, string>  $responses */
    private function fresh(array $responses, array $history): string
    {
        $lastAssistantMessage = collect($history)->reverse()->firstWhere('role', 'assistant')['content'] ?? null;

        foreach ($responses as $response) {
            if ($response !== $lastAssistantMessage) {
                return $response;
            }
        }

        return $responses[0];
    }

    private function normalize(string $message): string
    {
        return Str::ascii(mb_strtolower($message));
    }
}
