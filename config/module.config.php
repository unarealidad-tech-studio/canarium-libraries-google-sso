<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'googlesso_entities' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    0 => __DIR__ . '/../src/GoogleSSO/Entity',
                ),
            ),
            'orm_default' => array(
                'drivers' => array(
                    'GoogleSSO\\Entity' => 'googlesso_entities',
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
			'GoogleSSO' => 'GoogleSSO\Controller\GoogleSSOController',
			'GoogleSSOApi' => 'GoogleSSO\Controller\ApiController',
        ),
    ),

    'bjyauthorize' => array(
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'GoogleSSO', 'roles' => array('admin', 'user', 'guest')),
                array('controller' => 'GoogleSSOApi', 'roles' => array('admin', 'user', 'guest')),
            ),
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
            'googlesso' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/google/api',
                    'defaults' => array(
                        'controller' => 'GoogleSSOApi',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'GoogleSSOApi',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
	'view_helpers' => array(
		'invokables' => array(
			'CreateAuthUrl' => 'GoogleSSO\View\Helper\CreateAuthUrl'
		)
	),
);