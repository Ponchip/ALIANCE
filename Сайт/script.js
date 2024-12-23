let p = document.getElementById('titl');
const paragraph = p.nextElementSibling;
paragraph.classList.remove('hidden');
        setTimeout(() => {
            paragraph.classList.add('show');
        }, 400); // Небольшая задержка для плавного эффекта

    function toggleParagraph(header) {
        // Находим родительский элемент <section>
        const section = header.parentElement; // Предполагаем, что заголовок находится внутри <section>
        const paragraphs = section.querySelectorAll('p'); // Находим все абзацы внутри <section>
        const images = section.querySelectorAll('img'); // Находим все изображения внутри <section>
    
        paragraphs.forEach(paragraph => {
            if (paragraph.classList.contains('hidden')) {
                paragraph.classList.remove('hidden');
                setTimeout(() => {
                    paragraph.classList.add('show');
                }, 10); // Небольшая задержка для плавного эффекта
            } else {
                paragraph.classList.remove('show');
                setTimeout(() => {
                    paragraph.classList.add('hidden');
                }, 10); // Задержка до завершения анимации
            }
        });
    
        images.forEach(image => {
            if (image.parentElement.classList.contains('hidden')) {
                image.parentElement.classList.remove('hidden');
                setTimeout(() => {
                    image.parentElement.classList.add('show');
                }, 10); // Небольшая задержка для плавного эффекта
            } else {
                image.parentElement.classList.remove('show');
                setTimeout(() => {
                    image.parentElement.classList.add('hidden');
                }, 10); // Задержка до завершения анимации
            }
        });
    }


    function generateMultiplicationTable() {
        const number = document.getElementById('numberInput').value;
        const resultDiv = document.getElementById('result');

        if (number === '') {
            resultDiv.innerHTML = 'Пожалуйста, введите число.';
            return;
        }

        let table = `<h3>Таблица умножения на ${number}</h3><ul>`;
        for (let i = 1; i <= 10; i++) {
            table += `<li>${number} x ${i} = ${number * i}</li>`;
        }
        table += '</ul>';
        resultDiv.innerHTML = table;
    }