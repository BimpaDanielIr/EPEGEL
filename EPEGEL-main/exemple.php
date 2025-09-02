<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPEGL - Gestion Administrative</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styles CSS inchangés pour le front-end */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animated-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .sidebar { transition: width 0.3s ease-in-out; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .nav-text, .sidebar.collapsed .logo-text { display: none; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        .modal-overlay { position: fixed; inset: 0; background-color: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 50; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content { background: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal-overlay.active .modal-content { transform: scale(1); }
        .toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 2px; padding: 16px; position: fixed; z-index: 100; bottom: 30px; left: 50%; margin-left: -125px; transition: opacity 0.3s; opacity: 0; }
        .toast.show { visibility: visible; opacity: 1; }
        .toast.success { background-color: #4CAF50; }
        .toast.error { background-color: #F44336; }
        .toast.info { background-color: #2196F3; }
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .main-content {
            min-height: calc(100vh - 64px);
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        }
        .sidebar .menu-extra {
            margin-top: 2rem;
            border-top: 1px solid #374151;
            padding-top: 1rem;
        }
        .sidebar .menu-extra .nav-link {
            color: #fbbf24;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="loginPage" class="flex items-center justify-center min-h-screen bg-gray-200" style="display: flex;">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md animated-fade-in">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">EPEGL - Gestion</h1>
            
            <form id="loginForm" class="space-y-4">
                <h2 class="text-2xl font-semibold text-center text-gray-700 mb-4">Connexion</h2>
                <div class="mb-4">
                    <label for="login-identifier" class="block text-gray-700 text-sm font-bold mb-2">Nom d'utilisateur ou Matricule</label>
                    <input type="text" id="login-identifier" name="identifier" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Nom d'école ou matricule" required>
                </div>
                <div class="mb-6">
                    <label for="login-password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" id="login-password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">Se connecter</button>
                <p class="text-center text-gray-500 text-sm mt-4">Pas encore de compte ? <a href="#" id="showRegister" class="text-blue-600 hover:underline">Inscrivez-vous ici</a></p>
            </form>

            <form id="registerForm" class="space-y-4" style="display: none;">
                <h2 class="text-2xl font-semibold text-center text-gray-700 mb-4">Inscription</h2>
                <div class="mb-4">
                    <label for="register-nom" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                    <input type="text" id="register-nom" name="nom" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="register-prenom" class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
                    <input type="text" id="register-prenom" name="prenom" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="register-classe" class="block text-gray-700 text-sm font-bold mb-2">Classe</label>
                    <input type="text" id="register-classe" name="classe" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-6">
                    <label for="register-password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" id="register-password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">S'inscrire</button>
                <p class="text-center text-gray-500 text-sm mt-4">Vous avez déjà un compte ? <a href="#" id="showLogin" class="text-green-600 hover:underline">Connectez-vous</a></p>
            </form>
        </div>
    </div>

    <div id="mainApp" class="flex min-h-screen" style="display: none;">
        <aside class="sidebar w-64 p-4 bg-gray-800 text-white flex flex-col transition-all duration-300">
            <div class="flex items-center mb-6">
                <img src="https://placehold.co/40x40/fff/000.png" alt="Logo EPEGL" class="rounded-full mr-3">
                <span class="logo-text text-xl font-bold">EPEGL</span>
            </div>
            <nav class="flex-1">
                <ul id="adminMenu" class="space-y-2" style="display: none;">
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="dashboard"><i class="fas fa-chart-line w-6 text-center mr-3"></i><span class="nav-text">Tableau de Bord</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="students"><i class="fas fa-user-graduate w-6 text-center mr-3"></i><span class="nav-text">Étudiants</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="teachers"><i class="fas fa-chalkboard-teacher w-6 text-center mr-3"></i><span class="nav-text">Enseignants</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="courses"><i class="fas fa-book w-6 text-center mr-3"></i><span class="nav-text">Cours</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="billing"><i class="fas fa-file-invoice-dollar w-6 text-center mr-3"></i><span class="nav-text">Paiements</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="export"><i class="fas fa-download w-6 text-center mr-3"></i><span class="nav-text">Export</span></a></li>
                </ul>
                <ul id="studentMenu" class="space-y-2" style="display: none;">
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="dashboard-student"><i class="fas fa-chart-line w-6 text-center mr-3"></i><span class="nav-text">Mon Tableau de Bord</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="my-notes"><i class="fas fa-clipboard-list w-6 text-center mr-3"></i><span class="nav-text">Mes Notes</span></a></li>
                    <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-gray-700 transition-colors duration-200" data-page="my-payments"><i class="fas fa-file-invoice-dollar w-6 text-center mr-3"></i><span class="nav-text">Mes Paiements</span></a></li>
                </ul>
                <div class="menu-extra">
                    <ul class="space-y-2">
                        <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-yellow-700 transition-colors duration-200" data-page="schedule"><i class="fas fa-calendar-alt w-6 text-center mr-3"></i><span class="nav-text">Emploi du temps</span></a></li>
                        <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-yellow-700 transition-colors duration-200" data-page="notifications"><i class="fas fa-bell w-6 text-center mr-3"></i><span class="nav-text">Notifications</span></a></li>
                        <li><a href="#" class="nav-link flex items-center p-2 rounded-md hover:bg-yellow-700 transition-colors duration-200" data-page="subjects"><i class="fas fa-book-open w-6 text-center mr-3"></i><span class="nav-text">Matières</span></a></li>
                    </ul>
                </div>
            </nav>
            <div class="mt-auto">
                <button id="logoutBtn" class="w-full flex items-center justify-center p-2 rounded-md bg-red-600 hover:bg-red-700 text-white">
                    <i class="fas fa-sign-out-alt mr-3"></i><span>Déconnexion</span>
                </button>
            </div>
        </aside>

        <main class="main-content flex-1 p-8 mx-2 my-4">
            <header class="flex justify-between items-center pb-4 border-b border-gray-200 mb-8">
                <h2 id="pageTitle" class="text-3xl font-bold text-gray-800"></h2>
                <div class="flex items-center space-x-4">
                    <span id="userName" class="text-gray-700 font-medium text-right"></span>
                    <button id="toggleSidebar" class="p-2 rounded-md bg-gray-200 text-gray-600 block lg:hidden"><i class="fas fa-bars"></i></button>
                </div>
            </header>
            <div id="content-area">
                <!-- Les nouvelles pages seront chargées ici -->
            </div>
        </main>
    </div>

    <div id="mainModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-bold"></h3>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>
            <form id="modalForm" class="space-y-4">
                </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="app.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    // ...existing code...
    function handleLogin(user) {
        document.getElementById('loginPage').style.display = 'none';
        document.getElementById('mainApp').style.display = 'flex';
        document.getElementById('userName').textContent = user.name;
        // ...existing code...

        if (user.role === 'etudiant') {
            // Charger les données dynamiques étudiant
            fetch('get_student_data.php?action=dashboard_stats')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('studentName').textContent = user.name;
                        document.getElementById('studentClass').textContent = data.stats.classe || '-';
                        document.getElementById('studentLevel').textContent = data.stats.niveau || '-';
                        document.getElementById('studentYear').textContent = data.stats.annee_scolaire || '-';
                        document.getElementById('totalFees').textContent = data.stats.montant_total || '-';
                        document.getElementById('paidFees').textContent = data.stats.montant_paye || '-';
                        document.getElementById('remainingFees').textContent = data.stats.solde_du || '-';
                        document.getElementById('averageGrade').textContent = data.stats.moyenne_generale || '-';
                        // ...autres champs...
                    }
                });
            // Charger les notes
            fetch('get_student_data.php?action=notes')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const gradesTableBody = document.getElementById('gradesTableBody');
                        gradesTableBody.innerHTML = '';
                        data.notes.forEach(note => {
                            const row = gradesTableBody.insertRow();
                            row.innerHTML = `
                                <td>${note.cours_titre}</td>
                                <td>${note.note}</td>
                                <td>${note.coefficient}</td>
                                <td>-</td>
                                <td>${note.appreciation}</td>
                            `;
                        });
                    }
                });
            // ...idem pour emploi du temps, paiements...
        } else if (user.role === 'administrateur') {
            // Charger les stats admin
            fetch('get_admin_stats.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('adminStudentsCount').textContent = data.stats.students;
                        document.getElementById('adminTeachersCount').textContent = data.stats.teachers;
                        document.getElementById('adminCoursesCount').textContent = data.stats.courses;
                        document.getElementById('adminRevenue').textContent = data.stats.revenue + ' FCFA';
                        // ...autres stats...
                    }
                });
            // Charger la liste des étudiants
            fetch('admin_students.php?action=list')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const studentsTableBody = document.getElementById('adminStudentsTableBody');
                        studentsTableBody.innerHTML = '';
                        data.students.forEach(student => {
                            const row = studentsTableBody.insertRow();
                            row.innerHTML = `
                                <td>${student.numero_etudiant}</td>
                                <td>${student.nom} ${student.prenom}</td>
                                <td>${student.classe}</td>
                                <td>${student.est_actif ? 'Actif' : 'Inactif'}</td>
                                <td>
                                    <button class="text-blue-600"><i class="fas fa-eye"></i></button>
                                    <button class="text-yellow-600"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-600"><i class="fas fa-trash"></i></button>
                                </td>
                            `;
                        });
                    }
                });
            // ...idem pour enseignants, cours, facturation...
        }
    }
    // ...existing code...
});
</script>
</body>
</html>