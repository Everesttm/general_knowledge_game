document.addEventListener('DOMContentLoaded', function() {
    initGame();
    startTimer();
    fetchInitialScore();

    function initGame() {
        loadCategories();
        document.getElementById('help').onclick = showHelp;
        document.getElementById('reset-game').addEventListener('click', resetGame);
    }

    function fetchInitialScore() {
        fetch('game.php?action=getScore')
        .then(response => response.json())
        .then(data => {
            if (data.score !== undefined) {
                document.getElementById('current-score').textContent = data.score;
            }
        })
        .catch(error => console.error('Error fetching initial score:', error));
    }

    function loadCategories() {
        fetch('game.php?action=getCategories')
            .then(response => response.json())
            .then(categories => {
                const categoryContainer = document.getElementById('category-container');
                categoryContainer.innerHTML = '';
                categories.forEach(category => {
                    const categoryBtn = document.createElement('button');
                    categoryBtn.className = 'category-btn';
                    categoryBtn.textContent = category.name;
                    categoryBtn.onclick = () => {
                        document.getElementById('selected-category').textContent = 'Selected Category: ' + category.name;
                        loadQuestions(category.id);
                    };
                    categoryContainer.appendChild(categoryBtn);
                });
            })
            .catch(error => console.error('Error loading categories:', error));
    }
    
    function loadQuestions(categoryId) {
        fetch(`game.php?action=getQuestions&categoryId=${categoryId}`)
            .then(response => response.json())
            .then(questions => {
                const questionContainer = document.getElementById('question-container');
                questionContainer.innerHTML = '';
                questions.forEach(question => {
                    const questionBtn = document.createElement('button');
                    questionBtn.className = 'question-btn';
                    questionBtn.textContent = `Question ${question.value} Points`;
                    questionBtn.setAttribute('data-question-id', question.id);
    
    
                    questionBtn.onclick = function() {
                        displayQuestion(question);
                        questionBtn.remove();
                    };
    
                    questionContainer.appendChild(questionBtn);
                });
            })
            .catch(error => console.error('Error loading questions:', error));
    }
    

    function displayQuestion(question) {
        const userAnswer = prompt(question.question);
        if (userAnswer !== null) {
            checkAnswer(userAnswer.trim().toLowerCase(), question);
        }
        markQuestionAsAnswered(question.id);
    }
    
    function markQuestionAsAnswered(questionId) {
        fetch('game.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'question_id=' + questionId
        })
        .then(response => response.json())
        .then(data => {
            console.log('Question marked as answered', data);
            // Optionally disable the button for this question
            const questionBtn = document.querySelector('button[data-question-id="' + questionId + '"]');
            if (questionBtn) {
                questionBtn.disabled = true;
            }
        })
        .catch(error => console.error('Error marking question as answered:', error));
    }
    

    function checkAnswer(userAnswer, question) {
        if (userAnswer === question.answer.toLowerCase()) {
            updateScore(question.value);
            alert('Correct!');
        } else {
            alert(`Incorrect. The correct answer is: ${question.answer}`);
        }
    }

    function updateScore(score) {
        const scoreElement = document.getElementById('current-score');
        let currentScore = parseInt(scoreElement.textContent) || 0;
        currentScore += parseInt(score);
        scoreElement.textContent = currentScore.toString();
        correctAnswer(score);
    }

    function correctAnswer(score) {
        fetch('game.php?action=updateScore', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'score=' + score
        })
        .then(response => response.json())
        .then(data => {
            console.log('Score updated on server:', data);
        })
        .catch(error => console.error('Error updating score on server:', error));
    }

    function startTimer() {
        const startTime = Date.now();
        const timerElement = document.getElementById('game-timer').querySelector('span');
        setInterval(() => {
            const elapsedTime = Date.now() - startTime;
            const hours = Math.floor(elapsedTime / 3600000).toString().padStart(2, '0');
            const minutes = Math.floor((elapsedTime % 3600000) / 60000).toString().padStart(2, '0');
            const seconds = Math.floor((elapsedTime % 60000) / 1000).toString().padStart(2, '0');
            timerElement.textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);
    }

    function resetGame() {
        fetch('reset_game.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('New game started. Your score is reset.');
                document.getElementById('current-score').textContent = '0'; // Reset the score display
            } else {
                alert('Failed to start a new game.');
            }
        });
    }

    function showHelp() {
        alert("Welcome to General Knowledge Game! Select a category and choose a question to answer. Earn points by answering correctly.");
    }
});
