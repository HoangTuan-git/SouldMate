// Dexuat page JavaScript - Tinder-style card stack
(function () {
    const container = document.getElementById('cardContainer');
    if (!container) return;

    const allCards = Array.from(container.querySelectorAll('.card'));
    if (allCards.length === 0) {
        container.classList.add('is-empty');
        return;
    }

    // Xếp chồng: card đầu on top
    allCards.forEach((c, i) => {
        c.style.zIndex = (allCards.length - i).toString();
        c.style.opacity = (i === 0 ? '1' : '1'); // thẻ dưới vẫn thấy ở mép
        c.style.transform = `translateY(${i * 4}px) scale(${1 - i * 0.02})`;
    });

    let idx = 0;

    function updateStack() {
        const rest = allCards.slice(idx);
        if (rest.length === 0) {
            container.classList.add('is-empty');
            return;
        }
        container.classList.remove('is-empty');
        rest.forEach((c, i) => {
            c.style.transition = 'transform .35s ease, opacity .35s ease';
            c.style.zIndex = (rest.length - i).toString();
            c.style.opacity = '1';
            c.style.pointerEvents = (i === 0 ? 'auto' : 'none');
            c.style.transform = `translateY(${i * 4}px) scale(${1 - i * 0.02})`;
        });
    }

    function act(direction) {
        const card = allCards[idx];
        if (!card) return;

        // Animate ra 2 hướng
        const dx = (direction === 'like') ? 160 : -160;
        const rot = (direction === 'like') ? 15 : -15;
        card.style.transform = `translate(${dx}%, -10%) rotate(${rot}deg)`;
        card.style.opacity = '0';

        // Sang thẻ kế
        setTimeout(() => {
            idx++;
            updateStack();
        }, 350);
    }

    // Gắn sự kiện
    allCards.forEach(c => {
        // Cho phép nút Like submit form thực sự (không preventDefault)
        c.querySelector('.btn-skip')?.addEventListener('click', () => act('skip'));
    });

    updateStack();
})();