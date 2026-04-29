(function(){
    const navSetting = document.getElementById('navSetting');
    const sideNav = document.getElementById('sideNav');
    const closeSideNav = document.getElementById('closeSideNav');
    const overlay = document.getElementById('overlay');
    const navMonitoring = document.getElementById('navMonitoring');
    const monitoringArea = document.getElementById('monitorArea');
    
    navMonitoring.addEventListener('click', function () {
        console.log('navMonitoring clicked');
        monitoringArea.classList.toggle('hidden');
        overlay.classList.add('active');
    });

    navSetting.addEventListener('click', function() {
        sideNav.classList.add('active');
        overlay.classList.add('active');
        monitoringArea.classList.add('hidden');
    });

    closeSideNav.addEventListener('click', function() {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
    });

    overlay.addEventListener('click', function() {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
        monitoringArea.classList.add('hidden');
    });

    const sideNavItems = document.querySelectorAll('.side-nav-item');
    sideNavItems.forEach(item => {
        item.addEventListener('click', function() {
            sideNavItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.getElementById('logoutForm').addEventListener('submit', function(e) {
        console.log('Logout form submitted');
        if (!confirm('Apakah Anda yakin ingin logout?')) {
            e.preventDefault();
            return;
        }
        
        localStorage.removeItem('access_token');
    });
})();