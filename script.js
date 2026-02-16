document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const messageBox = document.getElementById('messageBox');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    
    // Validação básica
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    
    if (!name || !email) {
        showMessage('Por favor, preencha todos os campos.', 'error');
        return;
    }
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Por favor, insira um e-mail válido.', 'error');
        return;
    }
    
    // Desabilita o botão durante o envio
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="btn-text">Enviando...</span>';
    
    try {
        const response = await fetch('save_newsletter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Cadastro realizado com sucesso! Você será avisado em breve.', 'success');
            nameInput.value = '';
            emailInput.value = '';
        } else {
            showMessage(result.message || 'Erro ao cadastrar. Tente novamente.', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showMessage('Erro ao enviar os dados. Verifique sua conexão e tente novamente.', 'error');
    } finally {
        // Reabilita o botão
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <span class="btn-text">Quero ser avisado</span>
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
    }
});

function showMessage(message, type) {
    const messageBox = document.getElementById('messageBox');
    messageBox.textContent = message;
    messageBox.className = `message-box show ${type}`;
    
    // Remove a mensagem após 5 segundos
    setTimeout(() => {
        messageBox.classList.remove('show');
    }, 5000);
}