# GoogleSSO

Enables Canarium to have a Google Login/Sign up functionality. This module also supports multiple associated accounts per Canarium User.

# Installation

Install via composer: 

`composer require unarealidad/canarium-libraries-google-sso dev-master`

Add `GoogleSSO` to your Appmaster's `config/application.config.php` or your Appinstance's `config/instance.config.php` under the key `modules`

Copy the global config `data/googlesso.global.php.dist` to your Appinstance's `config/autoload/` directory and remove the `.dist` extension. This is the global configuration.

Copy the sample config `data/googlesso.local.php.dist` to your Appinstance's `config/autoload/` directory and remove the `.dist` extension.

Go to your Appinstance directory and run the following to update your database:

`./doctrine-module orm:schema-tool:update --force`

# Configuration

Configuration main key: `googlesso`
Sample Config file: `data/googlesso.global.php.dist`, `data/googlesso.local.php.dist`

Config Item | Sample Value | Required | Description
--- | --- | --- | ---
client_id | '4847851890871-6gjh8mc244tmct68gs72' | true | The google client id used to connect to the api
client_secret | 'ExW6DGwai_sI9Nv' | true | The google client secret used to connect to the api
scope | array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/userinfo.email') | true | The google permissions to request to the user.
redirect_uri | 'http://samplesite.com/oauth2callback' | true | The redirect URL to be used in the Google authentication. This will always be the /oauth2callback route of your site.
auth_class_service | 'GoogleSSO\Authentication\ForceLogin' | false | The authentication class to use. This can either be `GoogleSSO\Authentication\ForceLogin` or `GoogleSSO\Authentication\ConnectedAccount`. Defaults to `GoogleSSO\Authentication\ForceLogin`.
use_connected_accounts | false | false | Whether to allow connecting of multiple accounts to a single canarium login

# Exposed Pages

URL | Template | Access | Description
----- | ----- | ----- | -----
/oauth2callback | _None_ | Guest | The url that google will use to pass the authentication code after a sucessful login

# Additional Customization

## Enabling Connected Accounts

By setting the auth_class_service to `GoogleSSO\Authentication\ConnectedAccount` and use_connected_accounts to `true`, you can associate multiple google accounts into one canarium user. This feature is still under development and is not flexible enough for multiple use cases.

# Exposed Services

_None_
