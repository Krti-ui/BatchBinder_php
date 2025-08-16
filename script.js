// Fun facts array
const funFacts = [
    "Did you know? The human brain processes visual information 60,000 times faster than text.",
    "Reading for just 6 minutes can reduce stress levels by up to 68%.",
    "The average college student spends 24 hours per week studying.",
    "Taking notes by hand improves memory and learning.",
    "The Pomodoro Technique was invented by Francesco Cirillo in the late 1980s.",
    "The human attention span is typically between 10-40 minutes.",
    "Regular study breaks improve learning efficiency by 50%.",
    "Mind mapping can improve memory retention by up to 32%.",
    "The 5-minute rule: If a task takes less than 5 minutes, do it immediately.",
    "Multitasking can reduce productivity by up to 40%.",
    "The best time to learn new information is right before sleep.",
    "Blue light from screens can disrupt your sleep cycle if viewed before bedtime.",
    "Exercise increases brain function and memory retention.",
    "The 'spacing effect' shows that distributed learning is more effective than cramming.",
    "Teaching someone else is one of the most effective ways to learn."
];

// Particle effect colors
const lightColors = ['#38bdf8', '#0ea5e9', '#fbbf24', '#f472b6', '#fff'];
const darkColors = ['#38bdf8', '#0ea5e9', '#10b981', '#f472b6', '#facc15'];
let currentColors = lightColors;

// Theme management
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

// Get theme from localStorage or system preference
function getSavedTheme() {
    const saved = localStorage.getItem('batchbinder-theme');
    if (saved === 'dark') return true;
    if (saved === 'light') return false;
    return prefersDark;
}

// Set theme across the application
function setTheme(dark) {
    document.body.classList.toggle('dark-mode', dark);
    themeToggle.classList.toggle('dark', dark);
    themeIcon.textContent = dark ? '☀️' : '🌙';
    currentColors = dark ? darkColors : lightColors;
    localStorage.setItem('batchbinder-theme', dark ? 'dark' : 'light');
    createParticles();
}

// Display random fact from the array
function displayRandomFact() {
    const factElement = document.getElementById('fact');
    const randomFact = funFacts[Math.floor(Math.random() * funFacts.length)];
    factElement.textContent = randomFact;
}

// Create floating particles effect
function createParticles() {
    const particlesBg = document.getElementById('particles-bg');
    particlesBg.innerHTML = '';
    const w = window.innerWidth;
    const h = window.innerHeight;
    
    // Adjust number of particles based on screen size
    const particleCount = Math.min(32, Math.floor(w * h / 20000));
    
    for (let i = 0; i < particleCount; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 12 + 6;
        p.style.width = p.style.height = size + 'px';
        p.style.left = Math.random() * w + 'px';
        p.style.background = currentColors[Math.floor(Math.random() * currentColors.length)];
        p.style.boxShadow = `0 0 ${size * 2}px ${size/2}px ${p.style.background}`;
        p.style.animationDelay = Math.random() * 4 + 's';
        particlesBg.appendChild(p);
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    // Set initial theme
    setTheme(getSavedTheme());
    
    // Display random fact
    displayRandomFact();
    
    // Create initial particles
    createParticles();
    
    // Set up event listeners
    themeToggle.addEventListener('click', () => {
        const isDark = !document.body.classList.contains('dark-mode');
        setTheme(isDark);
    });

    document.getElementById('continueBtn').addEventListener('click', function() {
        // Ensure theme is stored before redirect
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('batchbinder-theme', isDark ? 'dark' : 'light');
        
        // Fade out animation
        const elements = [
            document.getElementById('centerBox'),
            document.getElementById('particles-bg'),
            document.querySelector('.logo-container')
        ];
        
        elements.forEach(el => el.classList.add('fade-out'));
        
        // Redirect after animation completes
        setTimeout(() => {
            window.location.href = 'index2.html';
        }, 700);
    });
});

// Recreate particles on resize
window.addEventListener('resize', createParticles);