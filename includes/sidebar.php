<nav class="side-nav">
    <a href="" class="-intro-x flex items-center pt-5 sm:px-10">
        <img alt="Tinker Tailwind HTML Admin Template" class="w-24" src="https://maisenvios.com.br/wp-content/uploads//2021/03/LOGO_aSSINATURA.svg">
    </a>
    <div class="side-nav__devider my-6"></div>
    <ul>
        <li>
            <a href="<?php echo $_ENV['HOST_URL']; ?>/ready-shops.php" class="side-menu">
                <div class="side-menu__icon"> <i data-feather="box"></i> </div>
                <div class="side-menu__title">
                    Lojas 
                    <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
                </div>
            </a>
        </li>
        <li>
            <a href="<?php echo $_ENV['HOST_URL']; ?>/ready-users.php" class="side-menu">
                <div class="side-menu__icon"> <i data-feather="box"></i> </div>
                <div class="side-menu__title">
                    Usuário
                    <div class="side-menu__sub-icon "> <i data-feather="chevron-down"></i> </div>
                </div>
            </a>
        </li>
    </ul>
</nav>