(() => {
    const root = document.body;
    const messages = document.querySelector('#chat-messages');
    const form = document.querySelector('#chat-form');
    const input = document.querySelector('#chat-input');
    const sendButton = document.querySelector('#send-button');
    const consent = document.querySelector('#marketing-consent');
    const marketingInterestOptions = document.querySelector('#marketing-interest-options');
    const marketingInterestInputs = [...document.querySelectorAll('[data-marketing-interest]')];
    const channelTabs = [...document.querySelectorAll('.channel-tab')];
    const chatCard = document.querySelector('.chat-card');
    const channelCaption = document.querySelector('#channel-caption');
    const channelLabels = {
        site: { caption: 'Agente demonstrativo · conversa simulada', label: 'SITE' },
        whatsapp: { caption: 'WhatsApp · conversa simulada', label: 'WHATSAPP SIMULADO' },
        instagram: { caption: 'Instagram · conversa simulada', label: 'INSTAGRAM SIMULADO' },
    };
    const modeLabel = document.querySelector('#mode-label');
    const modeLight = document.querySelector('.mode-light');
    const quoteForm = document.querySelector('#quote-form');
    const quoteProduct = quoteForm.querySelector('[name="product_id"]');
    const quoteQuantity = quoteForm.querySelector('[name="quantity"]');
    const quotePurpose = quoteForm.querySelector('[name="purpose"]');
    const customizationToggle = document.querySelector('#quote-customization');
    const customizationField = document.querySelector('#customization-field');
    const customizationNote = document.querySelector('#customization-note');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const conversationKey = 'manu-ia-conversation-v2';
    let conversationId = localStorage.getItem(conversationKey) || crypto.randomUUID();
    let activeChannel = 'site';
    let waiting = false;

    localStorage.setItem(conversationKey, conversationId);

    const scrollToLatest = () => {
        messages.scrollTop = messages.scrollHeight;
    };

    const addMessage = (role, content, action = null, channel = activeChannel) => {
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
        channelName.textContent = role === 'user' ? 'VOCÊ' : `MANU · ${channelLabels[channel]?.label || 'AGENTE'}`;
        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${role === 'user' ? 'user-bubble' : 'assistant-bubble'}`;
        bubble.textContent = content;
        contentGroup.append(channelName, bubble);

        if (role === 'assistant' && (action?.type === 'quote' || action?.type === 'retention')) {
            const actionButton = document.createElement('button');
            actionButton.type = 'button';
            actionButton.className = 'assistant-next-step';
            actionButton.textContent = action.type === 'retention'
                ? 'Simular interesse em novidades'
                : action.product_id ? 'Montar simulação desta peça' : 'Abrir simulador de orçamento';
            actionButton.addEventListener('click', () => {
                if (action.type === 'retention') {
                    consent.checked = true;
                    updateMarketingInterestOptions(action.interests || []);
                    consent.focus();
                    return;
                }

                if (action.product_id) {
                    quoteProduct.value = String(action.product_id);
                }

                if (action.purpose) {
                    quotePurpose.value = action.purpose;
                }

                document.querySelector('#orcamento').scrollIntoView({ behavior: 'smooth' });
                (action.product_id ? quoteQuantity : quoteProduct).focus();
            });
            contentGroup.append(actionButton);
        }

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

    const updateMarketingInterestOptions = (selectedInterests = null) => {
        marketingInterestOptions.hidden = !consent.checked;

        if (selectedInterests !== null) {
            marketingInterestInputs.forEach((interest) => {
                interest.checked = selectedInterests.includes(interest.value);
            });
        } else if (!consent.checked) {
            marketingInterestInputs.forEach((interest) => {
                interest.checked = false;
            });
        }
    };

    consent.addEventListener('change', () => updateMarketingInterestOptions());

    const updateChannel = (channel) => {
        activeChannel = channelLabels[channel] ? channel : 'site';
        chatCard.dataset.channel = activeChannel;
        channelCaption.textContent = channelLabels[activeChannel].caption;
        channelTabs.forEach((tab) => {
            const isActive = tab.dataset.channel === activeChannel;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });
    };

    channelTabs.forEach((tab) => {
        tab.addEventListener('click', () => updateChannel(tab.dataset.channel));
    });

    const loadHistory = async () => {
        try {
            const response = await fetch(`${root.dataset.chatHistoryUrl}/${encodeURIComponent(conversationId)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) return;

            const data = await response.json();
            if (data.messages?.length) {
                messages.querySelectorAll('.message-row, .quick-prompts').forEach((item) => item.remove());
                data.messages.forEach((message) => addMessage(message.role, message.content, null, message.channel));
                consent.checked = Boolean(data.marketing_consent);
                updateMarketingInterestOptions(data.marketing_interests || []);
            }
        } catch {
            showError('Não foi possível carregar o histórico agora. Você ainda pode começar uma conversa.');
        }
    };

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
        addMessage('user', message, null, activeChannel);
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
                    marketing_interests: consent.checked
                        ? marketingInterestInputs.filter((interest) => interest.checked).map((interest) => interest.value)
                        : [],
                }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.errors?.message?.[0] || 'Não consegui enviar sua mensagem. Tente de novo.');
            }

            addMessage('assistant', data.message, data.action, activeChannel);
            modeLabel.textContent = data.mode === 'openai' ? 'IA conectada' : 'Modo demonstração';
            modeLight.classList.toggle('mode-live', data.mode === 'openai');
            consent.checked = Boolean(data.marketing_consent);
            updateMarketingInterestOptions(data.marketing_interests || []);
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
        updateMarketingInterestOptions([]);
        messages.querySelectorAll('.message-row, .quick-prompts, .chat-error').forEach((item) => item.remove());
        const greeting = document.createElement('div');
        greeting.className = 'message-row assistant-row';
        const avatar = document.createElement('span');
        avatar.className = 'message-avatar';
        avatar.textContent = 'm';
        const contentGroup = document.createElement('div');
        const label = document.createElement('div');
        label.className = 'message-channel';
        label.textContent = `MANU · ${channelLabels[activeChannel].label}`;
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble assistant-bubble';
        bubble.textContent = 'Oi, que bom ter você por aqui! 🌿 O que você gostaria de conhecer?';
        contentGroup.append(label, bubble);
        greeting.append(avatar, contentGroup);
        messages.append(greeting);
        input.focus();
    });

    const ratingButtons = [...document.querySelectorAll('[data-rating]')];
    const ratingResult = document.querySelector('#rating-result');
    let selectedRating = 0;

    ratingButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectedRating = Number(button.dataset.rating);
            ratingButtons.forEach((star) => {
                const isSelected = Number(star.dataset.rating) <= selectedRating;
                star.classList.toggle('selected', isSelected);
                star.setAttribute('aria-pressed', String(Number(star.dataset.rating) === selectedRating));
            });
        });
    });

    document.querySelector('#rating-form').addEventListener('submit', (event) => {
        event.preventDefault();
        if (!selectedRating) {
            ratingResult.textContent = 'Escolha uma nota para ver a avaliação demonstrativa.';
        } else {
            ratingResult.textContent = `Você escolheu ${selectedRating} ${selectedRating === 1 ? 'estrela' : 'estrelas'}. Obrigada por testar o protótipo!`;
        }
        ratingResult.hidden = false;
    });

    loadHistory();
})();
