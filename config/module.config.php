<?php
/**
 * @author Jethro Laviste
 */
 
return array(
    'controllers' => array(
        'invokables' => array(
			'GoogleSSO' => 'GoogleSSO\Controller\GoogleSSOController',
        ),
    ),
	'router' => array(
        'routes' => array(
			'oauth2callback' => array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/oauth2callback',
					'defaults' => array(
						'controller'    => 'GoogleSSO',
						'action'        => 'oauth2callback',
					),
				),
				'may_terminate' => true,
			),
        ),
    ),
	'view_helpers' => array(
		'invokables' => array(
			'CreateAuthUrl' => 'GoogleSSO\View\Helper\CreateAuthUrl'
		)
	),
);