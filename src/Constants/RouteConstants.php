<?php

namespace App\Constants;

class RouteConstants
{
    // -------------- SECURITY -------------- //
    const SECURITY_LOGIN = 'app.security.login';
    const SECURITY_LOGOUT = 'app.security.logout';
    const SECURITY_REGISTER = 'app.security.register';
    const SECURITY_VERIFY_EMAIL = 'app.security.verify.email';
    const SECURITY_FORGOT_PASSWORD_REQUEST = 'app.security.forgot.password.request';
    const SECURITY_CHECK_EMAIL = 'app.security.check.email';
    const SECURITY_RESET_PASSWORD = 'app.reset.password';

    // -------------- FRONT OFFICE -------------- //
    // Home
    const FRONTOFFICE_HOME = 'app.frontoffice.home';

    // Users
    const FRONTOFFICE_USERS = 'app.frontoffice.users';
    const FRONTOFFICE_USERS_SHOW = 'app.frontoffice.users.show';
    const FRONTOFFICE_USERS_EDIT = 'app.frontoffice.users.edit';
    const FRONTOFFICE_USERS_DELETE = 'app.frontoffice.users.delete';
    const FRONTOFFICE_USERS_EDIT_EMAIL = 'app.frontoffice.users.edit.email';
    const FRONTOFFICE_USERS_EDIT_PASSWORD = 'app.frontoffice.users.edit.password';
    const FRONTOFFICE_USERS_VERIFY_EMAIL = 'app.frontoffice.users.verify.email';
    const FRONTOFFICE_USERS_BAN = 'app.frontoffice.users.ban';
}