# Manu IA

Protótipo de atendimento e relacionamento com clientes para a ManuPekena, empresa de peças artesanais de biscuit inspiradas na fauna da Mata Atlântica.

## Estado atual

O protótipo entrega uma página responsiva com seis fotos reais de peças fornecidas pela ManuPekena, chat em três canais simulados (site, WhatsApp e Direct do Instagram), simulador de orçamento e painel administrativo para catálogo, conversas e orçamentos. As simulações compartilham o mesmo histórico. O painel mostra a preferência de novidades e permite consultar o histórico associado; não são coletados nome ou contato no protótipo.

O catálogo usa os nomes, detalhes e valores visíveis nas imagens fornecidas pelo usuário. A captura indica R$ 39 por peça avulsa; preço atual, personalização, disponibilidade e prazo precisam de confirmação da equipe. O destaque do site é o tatu-galinha; as peças do catálogo são boto-cinza, sapinho-da-montanha, tartaruga-verde, caranguejo-uçá, tamanduá-mirim e tatu-galinha. Nenhuma mensagem é enviada a contas reais. Sem chave da OpenAI, o chat usa um modo local contextual que entende perguntas sobre o catálogo, valores e perguntas de continuação; com `OPENAI_API_KEY`, o servidor chama a Responses API com as informações cadastradas e sem persistir a conversa no serviço (`store: false`).

## Tecnologias

- Laravel 13 e PHP 8.4 no servidor.
- HTML, CSS e JavaScript no site responsivo.
- MySQL 8.4 para a base planejada; SQLite está disponível para desenvolvimento rápido e testes.
- OpenAI Responses API chamada somente pelo backend Laravel.
- Os canais sociais e as automações n8n ainda não estão conectados.

## Executar localmente

Pré-requisitos instalados neste Mac: PHP 8.4, Composer 2.11, Node.js e MySQL 8.4.

1. Copie `.env.example` para `.env` se ainda não existir e gere a chave local:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

2. Inicie o MySQL instalado pelo Homebrew e crie a base e um usuário local:

   ```sh
   brew services start mysql@8.4
   mysql -uroot -e "CREATE DATABASE IF NOT EXISTS manu_ia CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci"
   mysql -uroot -e "CREATE USER IF NOT EXISTS 'manu_ia'@'127.0.0.1' IDENTIFIED BY 'change-me-for-local-development'; GRANT ALL PRIVILEGES ON manu_ia.* TO 'manu_ia'@'127.0.0.1'; FLUSH PRIVILEGES"
   ```

   Se trocar a senha no comando, atualize `DB_PASSWORD` no `.env` com o mesmo valor. Depois prepare as tabelas e carregue o catálogo das imagens fornecidas:

   ```sh
   php artisan migrate --seed
   ```

   Para testes automatizados, o Laravel usa SQLite em memória. A base MySQL local é usada ao executar a aplicação normalmente.

3. Inicie o servidor:

   ```sh
   php artisan serve
   ```

   Abra o endereço local mostrado no terminal.

4. Para ativar respostas pela OpenAI, configure `OPENAI_API_KEY` no `.env`. O valor nunca deve ser colocado no JavaScript, no HTML ou no Git. Sem a chave, o modo de demonstração continua disponível.

5. Para criar o primeiro acesso ao painel, execute `php artisan admin:create-user` e siga as perguntas no terminal. Depois, abra `/admin/login` no navegador. O cadastro de usuários é intencionalmente feito no terminal, sem uma tela pública de registro.

## Arquitetura deste primeiro fluxo

O navegador envia a mensagem para `POST /api/chat`. O controller valida o canal e o tamanho, salva as mensagens e a preferência de consentimento no Laravel, e chama `ChatAssistant`. O serviço lê os produtos ativos do banco e fornece esse catálogo como contexto para a API. O histórico é carregado em `GET /api/chat/{conversation}`; `DELETE` apaga a conversa demonstrativa. O identificador aleatório fica no `localStorage` do navegador e é compartilhado ao trocar de canal.

O agente ajuda a escolher uma peça e, quando a pessoa pede uma simulação, oferece um atalho que abre o simulador já com o produto e a finalidade identificados. O simulador envia produto, quantidade, personalização e data desejada para `POST /api/quotes`. O Laravel recalcula o total usando o valor exibido nas imagens e salva um registro `pending` ligado à conversa. Como o material não informa preço de personalização, ela não entra no cálculo e deve ser consultada com a equipe. Cada orçamento guarda os valores usados no cálculo para preservar a simulação se o catálogo mudar.

A chave fica no servidor, em variável de ambiente. Não informe dados pessoais no chat: as mensagens ficam salvas localmente no protótipo e, quando a integração estiver ativa, o texto enviado será processado pela OpenAI.

## Painel administrativo e CRM demonstrativo

O painel exige login e permite cadastrar, editar e arquivar produtos, consultar conversas e alterar a situação dos orçamentos. A página de conversas oferece filtro de consentimento e exibe o histórico, os orçamentos relacionados e temas de interesse demonstrativos (novas peças, ideias para presentear e curiosidades sobre a fauna). O identificador é aleatório; não há ficha de cliente com dados pessoais nesta etapa. O consentimento começa desativado. Mesmo quando marcado, nenhuma campanha ou contato externo é iniciado; esses temas apenas ilustram como o agente poderia apoiar ações de recompra no futuro.

A visão geral apresenta a evolução mensal das conversas e simulações dos últimos seis meses e as peças mais incluídas em orçamentos, usando apenas os registros existentes no protótipo. A situação “convertido” é uma marcação manual e não comprova uma compra; compras reais ainda não são registradas.

## Próximas etapas sugeridas

1. Ampliar o CRM com perfis de clientes, interesses, compras e gestão de consentimento.
2. Implementar registro de compras e indicadores de retenção e recompra.
3. Orquestrar follow-up demonstrativo e pós-venda no n8n, sem envio real.
4. Criar recomendações e segmentação a partir de dados revisados.
5. Revisar privacidade, segurança, acessibilidade e preparar a versão final para apresentação.

Consulte também [AGENTS.md](AGENTS.md) para as instruções permanentes de desenvolvimento deste repositório.
Para explicar e demonstrar o protótipo, consulte o [roteiro de apresentação](docs/roteiro-apresentacao.md).
