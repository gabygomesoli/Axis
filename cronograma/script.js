// Dados das semanas
const studyWeeks = [
    {
        title: "1ª Semana",
        progress: 0,
        subjects: [
            { id: 1, status: 'current', description: 'Interpretação de textos: compreender ideias explícitas e implícitas.' },
            { id: 2, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 3, status: 'locked', description: 'Química: estrutura atômica e molecular.' },
            { id: 4, status: 'locked', description: 'Biologia: genética e DNA.' },
            { id: 5, status: 'locked', description: 'Filosofia: pensamento crítico e lógica.' },
            { id: 6, status: 'locked', description: 'Matemática avançada: álgebra e cálculo.' },
            { id: 7, status: 'locked', description: 'Literatura: análise de obras clássicas.' },
            { id: 8, status: 'locked', description: 'Redação: técnicas de escrita e argumentação.' },
            { id: 9, status: 'locked', description: 'Biologia: microbiologia e células.' },
            { id: 10, status: 'locked', description: 'Geografia: geopolítica mundial.' },
            { id: 11, status: 'locked', description: 'Literatura: autores contemporâneos.' },
            { id: 12, status: 'locked', description: 'Matemática: geometria e trigonometria.' },
            { id: 13, status: 'locked', description: 'Física: estrutura atômica e nuclear.' },
            { id: 14, status: 'locked', description: 'Química: reações químicas e equilíbrio.' },
            { id: 15, status: 'locked', description: 'Redação: dissertação e técnicas de conclusão.' }
        ]
    },
    {
        title: "2ª Semana",
        progress: 0,
        subjects: [
            { id: 1, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 2, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 3, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 4, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 5, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 6, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 7, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 8, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 9, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 10, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 11, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 12, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 13, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 14, status: 'locked', description: 'Matemática básica: operações fundamentais.' },
            { id: 15, status: 'locked', description: 'Matemática básica: operações fundamentais.' }
        ]
    },
    {
        title: "3ª Semana",
        progress: 0,
        subjects: [
            { id: 1, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 2, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 3, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 4, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 5, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 6, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 7, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 8, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 9, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 10, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 11, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 12, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 13, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 14, status: 'locked', description: 'História: período colonial brasileiro.' },
            { id: 15, status: 'locked', description: 'História: período colonial brasileiro.' }
        ]
    },
    {
        title: "4ª Semana",
        progress: 0,
        subjects: [
            { id: 1, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 2, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 3, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 4, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 5, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 6, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 7, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 8, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 9, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 10, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 11, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 12, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 13, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 14, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' },
            { id: 15, status: 'locked', description: 'Geografia: relevo e clima brasileiro.' }
        ]
    }
];

let currentWeekIndex = 0;
let progressChart = null;

const weekTitleElement = document.getElementById('week-title');
const weekDisplayElement = document.getElementById('week-display');
const prevWeekButton = document.getElementById('prev-week');
const nextWeekButton = document.getElementById('next-week');
const progressPercentageElement = document.getElementById('progress-percentage');
const statusElement = document.getElementById('status');
const subjects = document.querySelectorAll('.subject');
const starReward = document.getElementById('star-reward');
const achievementModal = document.getElementById('achievement-modal');
const closeModalButton = document.getElementById('close-modal');

function init() {
    const savedWeek = localStorage.getItem('currentWeekIndex');
    if (savedWeek !== null) {
        currentWeekIndex = parseInt(savedWeek);
    }
    
    if (currentWeekIndex === 0) {
        resetFirstWeek();
    }
    
    updateWeek();
    setupEventListeners();
    initProgressChart();
}

function resetFirstWeek() {
    studyWeeks[0].progress = 0;
    
    studyWeeks[0].subjects.forEach((subject, index) => {
        if (index === 0) {
            subject.status = 'current';
        } else {
            subject.status = 'locked';
        }
    });
    
    localStorage.removeItem('currentWeekIndex');
}

function setupEventListeners() {
    prevWeekButton.addEventListener('click', goToPreviousWeek);
    nextWeekButton.addEventListener('click', goToNextWeek);
    
    subjects.forEach(subject => {
        subject.addEventListener('click', handleSubjectClick);
    });
    
    closeModalButton.addEventListener('click', closeAchievementModal);
    
    achievementModal.addEventListener('click', function(e) {
        if (e.target === achievementModal) {
            closeAchievementModal();
        }
    });
}

function goToPreviousWeek() {
    if (currentWeekIndex > 0) {
        currentWeekIndex--;
        updateWeek();
    }
}

function goToNextWeek() {
    if (currentWeekIndex < studyWeeks.length - 1) {
        currentWeekIndex++;
        updateWeek();
    }
}

function checkAndUnlockNextWeek() {
    const currentWeek = studyWeeks[currentWeekIndex];
    const completedCount = currentWeek.subjects.filter(s => s.status === 'completed').length;
    
    if (completedCount === currentWeek.subjects.length) {
        if (currentWeekIndex < studyWeeks.length - 1) {
            const nextWeek = studyWeeks[currentWeekIndex + 1];
            
            if (nextWeek.subjects[0].status === 'locked') {
                nextWeek.subjects[0].status = 'current';
            }
            
            nextWeek.progress = 0;
            
            console.log(`🎉 ${currentWeek.title} concluída! ${nextWeek.title} desbloqueada!`);
        }
    }
}

function updateWeek() {
    const currentWeek = studyWeeks[currentWeekIndex];
    
    weekTitleElement.textContent = currentWeek.title;
    weekDisplayElement.textContent = currentWeek.title;
    
    const isCurrentWeekCompleted = currentWeek.subjects.every(s => s.status === 'completed');
    
    prevWeekButton.disabled = currentWeekIndex === 0;
    nextWeekButton.disabled = currentWeekIndex === studyWeeks.length - 1 || !isCurrentWeekCompleted;
    
    updateSubjects(currentWeek.subjects);
    
    updateProgress(currentWeek);
    
    updateProgressChart(currentWeek.progress);
    
    updateStar(currentWeek);
    
    localStorage.setItem('currentWeekIndex', currentWeekIndex.toString());
}

function updateSubjects(subjectsData) {
    subjects.forEach((subject, index) => {
        if (index < subjectsData.length) {
            const subjectData = subjectsData[index];
            
            subject.className = 'subject';
            subject.classList.add(`subject-${index + 1}`);
            subject.classList.add(subjectData.status);
            
            const descriptionElement = subject.querySelector('.subject-description');
            if (descriptionElement) {
                descriptionElement.textContent = subjectData.description;
            }
        }
    });
}

function updateProgress(weekData) {
    const completedSubjects = weekData.subjects.filter(s => s.status === 'completed').length;
    const currentSubject = weekData.subjects.find(s => s.status === 'current');
    const remainingSubjects = weekData.subjects.filter(s => s.status === 'locked').length;
    
    const currentSubjectNumber = currentSubject ? currentSubject.id : 0;
    statusElement.textContent = `Matéria Atual: ${currentSubjectNumber} | Concluídas: ${completedSubjects} | Restantes: ${remainingSubjects}`;
}

function initProgressChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    progressChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [0, 100],
                backgroundColor: [
                    '#0493D7',
                    'rgba(255, 255, 255, 0.1)'
                ],
                borderWidth: 0,
                borderRadius: 10,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
}

function updateProgressChart(progress) {
    if (progressChart) {
        progressChart.data.datasets[0].data = [progress, 100 - progress];
        progressChart.update();
        progressPercentageElement.textContent = `${progress}%`;
    }
}

function updateStar(weekData) {
    const completedCount = weekData.subjects.filter(s => s.status === 'completed').length;
    const totalSubjects = weekData.subjects.length;
    
    if (completedCount === totalSubjects) {
        starReward.classList.remove('locked');
        starReward.classList.add('unlocked');
        
        const starImage = starReward.querySelector('.star-image');
        starImage.src = 'matérias/star.png';
        starImage.alt = 'Estrela Conquistada';
        
        const starDescription = starReward.querySelector('.star-description');
        starDescription.textContent = 'Parabéns! Você completou todas as matérias desta semana!';
    } else {
        starReward.classList.remove('unlocked');
        starReward.classList.add('locked');
        
        const starImage = starReward.querySelector('.star-image');
        starImage.src = 'matérias/star2.png';
        starImage.alt = 'Estrela de Conquista';
        
        const starDescription = starReward.querySelector('.star-description');
        starDescription.textContent = 'Conclua todas as matérias da semana para desbloquear esta conquista!';
    }
}

function handleSubjectClick(event) {
    const subject = event.currentTarget;
    const subjectId = parseInt(subject.querySelector('img').alt.split(' ')[1]);
    
    if (!subject.classList.contains('locked')) {
        alert(`Abrindo Matéria ${subjectId} da ${studyWeeks[currentWeekIndex].title}`);
        
        markSubjectAsCompleted(subjectId);
    } else {
        alert('Esta matéria está bloqueada. Conclua as matérias anteriores primeiro.');
    }
}

function markSubjectAsCompleted(subjectId) {
    const currentWeek = studyWeeks[currentWeekIndex];
    const subjectIndex = currentWeek.subjects.findIndex(s => s.id === subjectId);
    
    if (subjectIndex !== -1) {
        currentWeek.subjects[subjectIndex].status = 'completed';
        
        if (subjectIndex + 1 < currentWeek.subjects.length) {
            currentWeek.subjects[subjectIndex + 1].status = 'current';
        }
        
        const completedCount = currentWeek.subjects.filter(s => s.status === 'completed').length;
        currentWeek.progress = Math.round((completedCount / currentWeek.subjects.length) * 100);
        
        updateWeek();
        
        checkAndUnlockNextWeek();
        
        if (completedCount === currentWeek.subjects.length) {
            setTimeout(showAchievementModal, 500);
        }
    }
}

function showAchievementModal() {
    achievementModal.classList.add('active');
}

function closeAchievementModal() {
    achievementModal.classList.remove('active');
}

document.addEventListener('DOMContentLoaded', init);