
function hideElement(elem) {
    elem.style.display = 'none';
  }

function getVideo() {
    let id = document.getElementById('vid').innerHTML;
    let order = localStorage.getItem('commentOrder') || '1';
    let request;

    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4) {
            if (this.status === 200) {
                try {
                    const inform = JSON.parse(this.responseText);
                    console.log('Полученные данные:', inform);
                    // Форматируем данные
                    document.getElementById('vidName').innerHTML = inform.video.name;
                    document.getElementById('vidLikes').innerHTML = `Лайки: ${inform.video.likes}`;
                    document.getElementById('vidDesc').innerHTML = inform.video.description;
                    
                    // Добавляем стили для текста
                    document.getElementById('vidName').style.marginBottom = '1rem';
                    document.getElementById('vidLikes').style.color = '#666';
                    document.getElementById('vidDesc').style.color = '#333';
                    document.getElementById('vidDesc').style.lineHeight = '1.6';
                    
                    // Очищаем и заполняем комментарии
                    const commentsContainer = document.getElementById('comments');
                    commentsContainer.innerHTML = '';
                    
                    inform.comments.forEach(comment => {
                        const commentElement = document.createElement('div');
                        commentElement.className = 'comment';
                        commentElement.innerHTML = `
                            <div class="comment-header">
                                <span class="comment-author">${comment.name}</span>
                                <span class="comment-date">${comment.date}</span>
                            </div>
                            <div class="comment-content">${comment.comment}</div>
                            <div class="comment-actions">
                                <i class="bi bi-heart fs-6 me-2" 
                                   data-comment-id="${comment.id}"
                                   onclick="toggleCommentLike(this, ${comment.id}, ${comment.likes})"></i>
                                <span class="comment-likes" id="likes-${comment.id}">${comment.likes}</span>
                            </div>
                        `;
                        commentsContainer.appendChild(commentElement);
                        
                        // Добавляем обработчик для кнопки лайка
                        const heart = commentElement.querySelector('.bi-heart');
                        if (heart) {
                            heart.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const currentLikes = parseInt(this.nextElementSibling.textContent);
                                const commentId = this.dataset.commentId;
                                toggleCommentLike(this, commentId, currentLikes);
                            });
                        }
                    });
                } catch (error) {
                    console.error('Ошибка при парсинге JSON:', error);
                    console.error('Ответ сервера:', this.responseText);
                    alert('Ошибка при загрузке данных. Пожалуйста, попробуйте обновить страницу.');
                }
            } else {
                console.error('Ошибка сервера:', this.status);
                alert('Произошла ошибка при загрузке данных. Код ошибки: ' + this.status);
            }
        }
    }

    request.open("POST", "src/getVideo.php");
    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
    request.send("videoId=" + id + "&order=" + order);

}

// Обновление UI лайка
function updateLikeUI(heart, likesSpan, currentLikes, isActive) {
    heart.classList.add('animate');
    likesSpan.textContent = isActive ? currentLikes - 1 : currentLikes + 1;
    heart.classList.toggle('active');
    
    setTimeout(() => {
        heart.classList.remove('animate');
    }, 500);
}

// Откат изменений UI при ошибке
function rollbackLikeUI(heart, likesSpan, currentLikes, isActive) {
    likesSpan.textContent = currentLikes;
    heart.classList.toggle('active');
}

// Глобальная функция для обработки лайков комментариев
function toggleCommentLike(heart, commentId, currentLikes) {
    // Проверяем параметры
    if (!heart || commentId === undefined || currentLikes === undefined) {
        console.error('Invalid parameters:', {
            heart: !!heart,
            commentId: commentId,
            currentLikes: currentLikes
        });
        return;
    }

    const likesSpan = document.getElementById(`likes-${commentId}`);
    const isActive = heart.classList.contains('active');

    // Обновляем UI сразу
    updateLikeUI(heart, likesSpan, currentLikes, isActive);

    // Отправляем запрос на сервер
    const request = new XMLHttpRequest();
    request.open('POST', 'src/toggleCommentLike.php');
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.onreadystatechange = function() {
        if (this.readyState === 4 && this.status !== 200) {
            // При ошибке откатываем изменения
            rollbackLikeUI(heart, likesSpan, currentLikes, isActive);
        }
    };
    
    // Логируем данные
    console.log('Sending data:', {
        comment_id: commentId,
        likes: currentLikes,
        action: isActive ? 'unlike' : 'like'
    });

    // Отправляем данные
    request.send(`comment_id=${commentId}&likes=${currentLikes}&action=${isActive ? 'unlike' : 'like'}`);
}

// Инициализация реакций
function initReactions() {
    const reactions = {
        'heart': {count: 1517, isActive: false},
        'chat': {count: 33, isActive: false},
        'bookmark': {count: 157, isActive: false}
    };

    const reactionElements = document.querySelectorAll('.reaction-item');

    reactionElements.forEach(element => {
        element.addEventListener('click', function () {
            const icon = this.querySelector('i');
            const count = this.querySelector('span');
            const type = icon.classList[1].split('bi-')[1];

            const isActive = reactions[type].isActive;
            reactions[type].isActive = !isActive;
            reactions[type].count += isActive ? -1 : 1;
            
            icon.classList.toggle('active');
            count.textContent = reactions[type].count;

            icon.classList.add('animate');
            setTimeout(() => {
                icon.classList.remove('animate');
            }, 500);
        });
    });
}

// Инициализация лайков комментариев
function initCommentLikes() {
    const hearts = document.querySelectorAll('.comment-actions .bi-heart');
    hearts.forEach(heart => {
        heart.addEventListener('click', function(e) {
            e.stopPropagation();
            const likesSpan = this.nextElementSibling;
            const commentId = this.dataset.commentId;
            const currentLikes = parseInt(likesSpan.textContent);
            toggleCommentLike(this, commentId, currentLikes);
        });
    });
}