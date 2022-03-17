<nav class="side-nav">
    <a href="" class="-intro-x flex items-center pt-5 sm:px-10">
        <img alt="Tinker Tailwind HTML Admin Template" class="w-24" src="https://maisenvios.com.br/wp-content/uploads//2021/03/LOGO_aSSINATURA.svg">
    </a>
    <div class="side-nav__devider my-6"></div>
    <ul>
        <li>
            <a href="<?php echo $_ENV['HOST_URL']; ?>/ready-shops.php" class="side-menu">
                <div class="side-menu__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="home" data-lucide="home" class="lucide lucide-home" data-darkreader-inline-stroke="" style="--darkreader-inline-stroke:currentColor;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <div class="side-menu__title">
                    Lojas 
                    <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
                </div>
            </a>
        </li>
        <li>
            <a href="<?php echo $_ENV['HOST_URL']; ?>/ready-users.php" class="side-menu">
                <div class="side-menu__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="users" data-lucide="users" class="lucide lucide-users" data-darkreader-inline-stroke="" style="--darkreader-inline-stroke:currentColor;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 00-3-3.87"></path><path d="M16 3.13a4 4 0 010 7.75"></path></svg>
                </div>
                <div class="side-menu__title">
                    Usuário
                    <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
                </div>
            </a>
        </li>
    </ul>
</nav>