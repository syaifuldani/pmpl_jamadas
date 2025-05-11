const burgerButton = document.getElementById('burger');
const navLinks = document.getElementById('nav-links');

burgerButton.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    burgerButton.classList.toggle('active');
});
