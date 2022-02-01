<!DOCTYPE html>
<!--
Template Name: Tinker - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en" class="light">
    <!-- BEGIN: Head -->
    <head>
        <meta charset="utf-8">
        <link href="dist/images/logo.svg" rel="shortcut icon">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Tinker admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
        <meta name="keywords" content="admin template, Tinker Admin Template, dashboard template, flat admin template, responsive admin template, web app">
        <meta name="author" content="LEFT4CODE">
        <title>Mais Envio</title>
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="dist/css/app.css" />
        <!-- END: CSS Assets-->
    </head>
    <!-- END: Head -->
    <body class="login">
        <div class="container-fluid ">
            <div class="block xl:grid grid-cols-2 gap-4">
                <!-- BEGIN: Login Info -->
                <div class="hidden xl:flex flex-col min-h-screen">
                    <a href="" class="-intro-x flex items-center pt-5 sm:px-10">
                        <img alt="Tinker Tailwind HTML Admin Template" class="w-24" src="https://maisenvios.com.br/wp-content/uploads//2021/03/LOGO_aSSINATURA.svg">
                    </a>
                    <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-dark-1 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                        <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                            Bem-vindo!
                        </h2>
                        <form action="/painel/php/users/login" id="login">
                          <div class="intro-x mt-8">
                              <input type="text" class="intro-x login__input form-control py-3 px-4 border-gray-300 block" placeholder="E-mail" name="email">
                              <input type="password" class="intro-x login__input form-control py-3 px-4 border-gray-300 block mt-4" placeholder="Senha" name="password">
                          </div>
                          <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                              <button class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">Login</button>
                          </div>
                        </form>
                    </div>
                </div>
                <!-- END: Login Info -->
                <!-- BEGIN: Login Form -->
                <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                    
                </div>
                <!-- END: Login Form -->
            </div>
        </div>
        <!-- BEGIN: JS Assets-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="dist/js/app.js"></script>
        <script src="dist/routes/login.js"></script>
        <!-- END: JS Assets-->
    </body>
</html>