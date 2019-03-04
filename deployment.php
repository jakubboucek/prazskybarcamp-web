<?php

$credentials = require __DIR__ . '/local/deployment.local.php';

return [
    'web' => [
        'remote' => 'ftps://134701.w1.wedos.net/www',
        'user' => $credentials['user'] ?? null,
        'password' => $credentials['password'] ?? null,
        'local' => '.',
        'test' => false,
        'ignore' => '
			/deployment.*
			/local
			!/local/.htaccess
			/log
			!/log/.htaccess
			/temp/*
			!/temp/.htaccess
			*/tests
			/www/upload
			/www/blog
		',

        'include' => '
        	/app
        	/app/*
        	/local
        	/local/.htaccess
        	/log
        	/log/.htaccess
        	/temp
        	/temp/.htaccess
        	/vendor
        	/vendor/*
        	/www
        	/www/*
        ',

        'allowDelete' => true,
        'purge' => [
            'temp/cache'
        ],
        'after' => [
            'remote: unlink log/email-sent'
        ],
    ],
    'log' => __DIR__ . '/log/deployment.log',
    'tempDir' => __DIR__ . '/temp',
    'colors' => true
];
