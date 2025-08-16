// script.js - Simplified
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.querySelector('.navbar-links');
    
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.navbar') && !e.target.closest('.navbar-links')) {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        }
    });

    // Handle viewport units on mobile
    function handleViewportUnits() {
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    window.addEventListener('resize', handleViewportUnits);
    handleViewportUnits();
    
    // Prevent zoom on input focus on mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        document.querySelector('meta[name="viewport"]').content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
    }
});

function showComingSoonMessage(feature) {
    const messages = [
        `🚧 Oops! ${feature} is still cooking in our development kitchen! 👨‍🍳`,
        `✨ Hold tight! ${feature} is getting dressed up for its big debut! 🎭`,
        `🎯 Almost there! ${feature} is doing final rehearsals! 🎬`,
        `🔮 ${feature} is learning some cool tricks before meeting you! 🎩`,
        `🚀 ${feature} is in its final countdown to launch! 🌟`
    ];
    alert(messages[Math.floor(Math.random() * messages.length)]);
}