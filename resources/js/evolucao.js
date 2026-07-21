// Importações globais se houver
import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    console.log("Sistema pronto e mega-profissional! 🚀");

    // -------------------------------
    // Hover animado e sombra em todos os cards
    // -------------------------------
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
        card.addEventListener('mouseenter', () => {
            card.style.transform = "translateY(-5px) scale(1.02)";
            card.style.boxShadow = "0 10px 25px rgba(0,0,0,0.3)";
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = "translateY(0) scale(1)";
            card.style.boxShadow = "0 4px 10px rgba(0,0,0,0.1)";
        });
    });

    // -------------------------------
    // Confirmação de logout em todos os links
    // -------------------------------
    const logoutForm = document.getElementById('logout-form');
    const logoutLinks = document.querySelectorAll('a[href$="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (!confirm('Deseja realmente sair do sistema?')) e.preventDefault();
        });
    });

    // -------------------------------
    // Foco automático nos campos de Evolução do Paciente
    // -------------------------------
    const descricaoField = document.getElementById('descricao');
    if (descricaoField) descricaoField.focus();

    // -------------------------------
    // Foco automático nos campos de SOAP
    // -------------------------------
    const soapField = document.getElementById('soap');
    if (soapField) soapField.focus();

    // -------------------------------
    // Foco automático nos campos de Prescrição
    // -------------------------------
    const prescricaoField = document.getElementById('prescricao');
    if (prescricaoField) prescricaoField.focus();
});
