// Функция для получения данных о видео
async function getVideoData(videoId) {
    try {
        const response = await fetch('/src/getVideo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `videoId=${videoId}`
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching video data:', error);
        throw error;
    }
}

// Пример использования
async function displayVideo(videoId) {
    try {
        const videoData = await getVideoData(videoId);
        
        // Отображаем основную информацию о видео
        const videoInfo = videoData.video;
        document.getElementById('video-name').textContent = videoInfo.name;
        document.getElementById('video-likes').textContent = videoInfo.likes;
        document.getElementById('video-description').textContent = videoInfo.description;

        // Отображаем комментарии
        const commentsContainer = document.getElementById('comments');
        commentsContainer.innerHTML = '';
        
        videoData.comments.forEach(comment => {
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.innerHTML = `
                <div class="comment-header">
                    <span class="comment-author">${comment.name}</span>
                    <span class="comment-date">${comment.date}</span>
                </div>
                <div class="comment-content">${comment.comment}</div>
                <div class="comment-likes">Лайки: ${comment.likes}</div>
            `;
            commentsContainer.appendChild(commentElement);
        });
    } catch (error) {
        console.error('Error displaying video:', error);
        alert('Ошибка при загрузке видео');
    }
}

// Пример вызова при загрузке страницы
window.onload = () => {
    const videoId = '1'; // замените на реальный ID видео
    displayVideo(videoId);
};
