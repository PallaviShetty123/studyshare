// AI Features and Advanced Search JS

const aiModal = document.getElementById('aiModal');
const aiModalBody = document.getElementById('aiModalBody');
let currentQuiz = null;
let currentQuestionIndex = 0;
let score = 0;

if (document.querySelector('.ai-close-btn')) {
    document.querySelector('.ai-close-btn').addEventListener('click', closeAiModal);
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === aiModal) {
        closeAiModal();
    }
});

function closeAiModal() {
    aiModal.classList.remove('show');
    setTimeout(() => {
        aiModalBody.innerHTML = '';
        currentQuiz = null;
    }, 300);
}

function showLoader(title) {
    aiModalBody.innerHTML = `
        <h2>${title}</h2>
        <p style="text-align: center; margin-top: 1rem;">Analyzing document...</p>
        <div class="loader"></div>
    `;
    aiModal.classList.add('show');
}

// ==========================================
// SMART NOTES
// ==========================================
function generateSmartNote(noteId, title, description) {
    showLoader('✨ Smart Notes');
    
    fetch('process_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            note_id: noteId,
            title: title,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderSmartNote(data.data, title);
        } else {
            showAiError(data.error || 'Failed to generate Smart Notes.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAiError('An error occurred while connecting to the AI server.');
    });
}

function renderSmartNote(data, title) {
    let html = `<h2>✨ Smart Notes: ${title}</h2>`;
    
    if (data.summary) {
        html += `<h3>📝 Summary</h3><p>${data.summary}</p>`;
    }
    
    if (data.key_points && data.key_points.length > 0) {
        html += `<h3>🔑 Key Points</h3><ul>`;
        data.key_points.forEach(point => {
            html += `<li>${point}</li>`;
        });
        html += `</ul>`;
    }
    
    if (data.flashcards && data.flashcards.length > 0) {
        html += `<h3>⚡ Flashcards</h3><ul>`;
        data.flashcards.forEach(card => {
            html += `<li><strong>Q:</strong> ${card.question}<br><strong>A:</strong> ${card.answer}</li>`;
        });
        html += `</ul>`;
    }
    
    aiModalBody.innerHTML = html;
}

// ==========================================
// QUIZ GENERATOR
// ==========================================
function generateQuiz(noteId, title, description) {
    showLoader('🧠 AI Quiz Generator');
    
    fetch('generate_quiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            note_id: noteId,
            title: title,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            currentQuiz = data.data;
            currentQuestionIndex = 0;
            score = 0;
            renderQuizQuestion();
        } else {
            showAiError(data.error || 'Failed to generate Quiz.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAiError('An error occurred while connecting to the AI server.');
    });
}

function renderQuizQuestion() {
    if (currentQuestionIndex >= currentQuiz.length) {
        renderQuizResults();
        return;
    }

    const q = currentQuiz[currentQuestionIndex];
    let html = `
        <h2>🧠 Question ${currentQuestionIndex + 1} of ${currentQuiz.length}</h2>
        <div class="quiz-question-container">
            <h3>${q.question}</h3>
            <div class="quiz-options">
    `;

    q.options.forEach((opt, idx) => {
        html += `<button class="quiz-option" onclick="selectQuizAnswer('${opt.replace(/'/g, "\\'")}', '${q.answer.replace(/'/g, "\\'")}', this)">${opt}</button>`;
    });

    html += `
            </div>
        </div>
        <button id="nextQuizBtn" class="ai-btn" style="display:none; float:right;" onclick="nextQuizQuestion()">Next Question ➡️</button>
    `;

    aiModalBody.innerHTML = html;
}

function selectQuizAnswer(selected, correct, btn) {
    const options = document.querySelectorAll('.quiz-option');
    options.forEach(opt => opt.disabled = true); // Disable all options

    if (selected === correct) {
        btn.classList.add('correct');
        score++;
    } else {
        btn.classList.add('wrong');
        // Find and highlight correct answer
        options.forEach(opt => {
            if (opt.textContent === correct) {
                opt.classList.add('correct');
            }
        });
    }

    document.getElementById('nextQuizBtn').style.display = 'block';
}

function nextQuizQuestion() {
    currentQuestionIndex++;
    renderQuizQuestion();
}

function renderQuizResults() {
    const percentage = Math.round((score / currentQuiz.length) * 100);
    let message = percentage >= 80 ? 'Excellent job! 🎉' : (percentage >= 50 ? 'Good effort! 👍' : 'Keep studying! 📚');
    
    aiModalBody.innerHTML = `
        <h2>🎯 Quiz Completed!</h2>
        <div class="quiz-score">${score} / ${currentQuiz.length}</div>
        <h3 style="text-align:center;">${message}</h3>
        <button class="ai-btn" style="display:block; margin: 2rem auto 0;" onclick="closeAiModal()">Close</button>
    `;
}

function showAiError(msg) {
    aiModalBody.innerHTML = `
        <h2>⚠️ Error</h2>
        <p>${msg}</p>
        <button class="ai-btn" style="margin-top: 1rem;" onclick="closeAiModal()">Close</button>
    `;
}

// ==========================================
// ADVANCED SEARCH & FILTERS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const subjectFilter = document.getElementById('subjectFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const fileTypeFilter = document.getElementById('fileTypeFilter');
    
    if (searchInput && subjectFilter) {
        const fetchFilteredNotes = window.debounce(() => {
            const container = document.getElementById('notes-container');
            const countSpan = document.getElementById('notes-count');
            
            // Show loading state
            container.innerHTML = '<div class="loader"></div>';
            
            const params = new URLSearchParams({
                search: searchInput.value,
                subject: subjectFilter.value,
                semester: semesterFilter.value,
                file_type: fileTypeFilter.value
            });
            
            fetch(`search_notes.php?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        countSpan.textContent = data.count;
                        container.innerHTML = data.html;
                        document.getElementById('notes-title').innerHTML = `${subjectFilter.value ? subjectFilter.value + ' Notes' : 'All Notes'} (<span id="notes-count">${data.count}</span>)`;
                    } else {
                        container.innerHTML = '<p class="no-data">Error loading notes.</p>';
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                    container.innerHTML = '<p class="no-data">Failed to connect to server.</p>';
                });
        }, 500);

        searchInput.addEventListener('input', fetchFilteredNotes);
        subjectFilter.addEventListener('change', fetchFilteredNotes);
        semesterFilter.addEventListener('change', fetchFilteredNotes);
        fileTypeFilter.addEventListener('change', fetchFilteredNotes);
    }
});
