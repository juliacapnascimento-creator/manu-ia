(() => {
    const root = document.body;
    const messages = document.querySelector('#chat-messages');
    const form = document.querySelector('#chat-form');
    const input = document.querySelector('#chat-input');
    const sendButton = document.querySelector('#send-button');
    const consent = document.querySelector('#marketing-consent');
    const modeLabel = document.querySelector('#mode-label');
    const modeLight = document.querySelector('.mode-light');
    const channelCaption = document.querySelector('#channel-caption');
    const chatCard = document.querySelector('.chat-card');
    const channelAgentName = document.querySelector('#channel-agent-name');
    const channelAgentStatus = document.querySelector('#channel-agent-status span');
    const quoteForm = document.querySelector('#quote-form');
    const customizationToggle = document.querySelector('#quote-customization');
    const customizationField = document.querySelector('#customization-field');
    const customizationNote = document.querySelector('#customization-note');
    const channelLabels = {
        site: 'Simulação de atendimento pelo site',
        whatsapp: 'Simulação de conversa pelo WhatsApp',
        instagram: 'Simulação de conversa pelo Direct do Instagram',
    };
    const channelDetails = {
        site: {
            caption: 'Atendimento demonstrativo pelo site · histórico compartilhado',
            name: 'Manu',
            status: 'por aqui agora',
            placeholder: 'Escreva sua mensagem...',
        },
        whatsapp: {
            caption: 'Simulação do WhatsApp · nenhuma mensagem é enviada',
            name: 'ManuPekena',
            status: 'WhatsApp demonstrativo',
            placeholder: 'Mensagem simulada no WhatsApp...',
        },
        instagram: {
            caption: 'Simulação do Direct · nenhuma mensagem é enviada',
            name: 'ManuPekena',
            status: 'Direct demonstrativo',
            placeholder: 'Mensagem simulada no Direct...',
        },
    };
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const conversationKey = 'manu-ia-conversation-v2';
    const channelKey = 'manu-ia-channel-v2';
    let conversationId = localStorage.getItem(conversationKey) || crypto.randomUUID();
    let activeChannel = localStorage.getItem(channelKey) || 'site';
    let waiting = false;

    localStorage.setItem(conversationKey, conversationId);

    const updateChannel = (channel) => {
        activeChannel = channel;
        localStorage.setItem(channelKey, channel);
        const details = channelDetails[channel];
        chatCard.dataset.channel = channel;
        channelCaption.textContent = details.caption;
        channelAgentName.textContent = details.name;
        channelAgentStatus.textContent = details.status;
        input.placeholder = details.placeholder;

        document.querySelectorAll('.channel-tab').forEach((tab) => {
            const selected = tab.dataset.channel === channel;
            tab.classList.toggle('active', selected);
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
    };

    const scrollToLatest = () => {
        messages.scrollTop = messages.scrollHeight;
    };

    const addMessage = (role, content, channel = activeChannel) => {
        const row = document.createElement('div');
        row.className = `message-row ${role === 'user' ? 'user-row' : 'assistant-row'}`;

        if (role === 'assistant') {
            const avatar = document.createElement('span');
            avatar.className = 'message-avatar';
            avatar.textContent = 'm';
            row.append(avatar);
        }

        const contentGroup = document.createElement('div');
        const channelName = document.createElement('div');
        channelName.className = `message-channel ${role === 'user' ? 'user-channel' : ''}`;
        channelName.textContent = channelLabels[channel].replace('Simulação de ', '');
        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${role === 'user' ? 'user-bubble' : 'assistant-bubble'}`;
        bubble.textContent = content;
        contentGroup.append(channelName, bubble);
        row.append(contentGroup);
        messages.append(row);
        scrollToLatest();
    };

    const showError = (message) => {
        messages.querySelector('.chat-error')?.remove();
        const error = document.createElement('p');
        error.className = 'chat-error';
        error.setAttribute('role', 'alert');
        error.textContent = message;
        messages.append(error);
        scrollToLatest();
    };

    const setWaiting = (isWaiting) => {
        waiting = isWaiting;
        sendButton.disabled = isWaiting;
        input.disabled = isWaiting;

        if (isWaiting) {
            const row = document.createElement('div');
            row.className = 'message-row assistant-row typing-row';
            row.innerHTML = '<span class="message-avatar">m</span><div class="message-bubble assistant-bubble typing-indicator" aria-label="Manu está respondendo"><i></i><i></i><i></i></div>';
            messages.append(row);
            scrollToLatest();
        } else {
            messages.querySelector('.typing-row')?.remove();
            input.disabled = false;
            input.focus();
        }
    };

    const loadHistory = async () => {
        updateChannel(activeChannel);

        try {
            const response = await fetch(`${root.dataset.chatHistoryUrl}/${encodeURIComponent(conversationId)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) return;

            const data = await response.json();
            if (data.messages?.length) {
                messages.querySelectorAll('.message-row, .quick-prompts').forEach((item) => item.remove());
                data.messages.forEach((message) => addMessage(message.role, message.content, message.channel));
                consent.checked = Boolean(data.marketing_consent);
            }
        } catch {
            showError('Não foi possível carregar o histórico agora. Você ainda pode começar uma conversa.');
        }
    };

    document.querySelectorAll('.channel-tab').forEach((tab) => {
        tab.addEventListener('click', () => updateChannel(tab.dataset.channel));
    });

    document.querySelector('#quick-prompts')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-prompt]');
        if (!button) return;
        input.value = button.dataset.prompt;
        form.requestSubmit();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (waiting) return;

        const message = input.value.trim();
        if (!message) return;

        messages.querySelector('.chat-error')?.remove();
        messages.querySelector('#quick-prompts')?.remove();
        addMessage('user', message);
        input.value = '';
        setWaiting(true);

        try {
            const response = await fetch(root.dataset.chatUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    channel: activeChannel,
                    message,
                    marketing_consent: consent.checked,
                }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.errors?.message?.[0] || 'Não consegui enviar sua mensagem. Tente de novo.');
            }

            addMessage('assistant', data.message);
            modeLabel.textContent = data.mode === 'openai' ? 'IA conectada' : 'Modo demonstração';
            modeLight.classList.toggle('mode-live', data.mode === 'openai');
        } catch (error) {
            showError(error.message || 'O chat está indisponível no momento. Tente novamente.');
        } finally {
            setWaiting(false);
        }
    });

    customizationToggle.addEventListener('change', () => {
        customizationField.hidden = !customizationToggle.checked;
        customizationNote.required = customizationToggle.checked;
    });

    const formatPrice = (amount) => new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(amount));

    quoteForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submitButton = quoteForm.querySelector('button[type="submit"]');
        const errorBefore = quoteForm.querySelector('.quote-error');
        const result = document.querySelector('#quote-result');
        errorBefore?.remove();
        submitButton.disabled = true;
        submitButton.querySelector('span:first-child').textContent = 'Calculando...';

        const formData = new FormData(quoteForm);
        const payload = {
            product_id: Number(formData.get('product_id')),
            quantity: Number(formData.get('quantity')),
            customization: customizationToggle.checked,
            customization_note: formData.get('customization_note') || null,
            purpose: formData.get('purpose'),
            desired_date: formData.get('desired_date') || null,
            conversation_id: conversationId,
        };

        try {
            const response = await fetch(root.dataset.quoteUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Não foi possível calcular este orçamento.');
            }

            document.querySelector('#quote-result-title').textContent = `${data.product} · ${data.quantity} ${data.quantity === 1 ? 'unidade' : 'unidades'}`;
            const personalization = data.customization
                ? ` · personalização ${formatPrice(data.customization_unit_price)}/unidade`
                : '';
            document.querySelector('#quote-result-detail').textContent = `Preço por unidade ${formatPrice(data.unit_price)}${personalization}`;
            document.querySelector('#quote-result-total').textContent = formatPrice(data.total);
            document.querySelector('#quote-result-note').textContent = `${data.message}${data.desired_date ? ` Data desejada anotada: ${data.desired_date}.` : ''} Referência ${data.quote_id.slice(0, 8)}.`;
            result.hidden = false;
        } catch (error) {
            const errorMessage = document.createElement('p');
            errorMessage.className = 'quote-error';
            errorMessage.setAttribute('role', 'alert');
            errorMessage.textContent = error.message || 'O simulador está indisponível no momento.';
            quoteForm.append(errorMessage);
        } finally {
            submitButton.disabled = false;
            submitButton.querySelector('span:first-child').textContent = 'Calcular simulação';
        }
    });

    document.querySelector('#new-chat').addEventListener('click', async () => {
        if (waiting) return;

        try {
            await fetch(`${root.dataset.chatHistoryUrl}/${encodeURIComponent(conversationId)}`, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            });
        } catch {
            // A new local conversation can still start if deleting old history fails.
        }

        conversationId = crypto.randomUUID();
        localStorage.setItem(conversationKey, conversationId);
        consent.checked = false;
        messages.querySelectorAll('.message-row, .quick-prompts, .chat-error').forEach((item) => item.remove());
        const greeting = document.createElement('div');
        greeting.className = 'message-row assistant-row';
        greeting.innerHTML = '<span class="message-avatar">m</span><div class="message-bubble assistant-bubble">Oi, que bom ter você por aqui! 🌿<br>O que você gostaria de conhecer?</div>';
        messages.append(greeting);
        input.focus();
    });

    const ratingForm = document.querySelector('#rating-form');
    const ratingResult = document.querySelector('#rating-result');
    let selectedRating = 0;

    ratingForm.querySelectorAll('[data-rating]').forEach((button) => {
        button.addEventListener('click', () => {
            selectedRating = Number(button.dataset.rating);
            ratingForm.querySelectorAll('[data-rating]').forEach((star) => {
                const selected = Number(star.dataset.rating) <= selectedRating;
                star.classList.toggle('selected', selected);
                star.setAttribute('aria-pressed', Number(star.dataset.rating) === selectedRating ? 'true' : 'false');
            });
            ratingResult.hidden = true;
        });
    });

    ratingForm.addEventListener('submit', (event) => {
        event.preventDefault();
        ratingResult.textContent = selectedRating
            ? `Obrigado por avaliar esta demonstração com ${selectedRating} ${selectedRating === 1 ? 'estrela' : 'estrelas'}! Sua resposta não foi salva.`
            : 'Escolha uma estrela para mostrar a avaliação.';
        ratingResult.hidden = false;
    });

    loadHistory();
})();
