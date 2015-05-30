<?php

return array(
        'enabled' => true,
        'template' => 'toolbar',
        'tabs' => array(
                'messages' => array(
                        'name' => 'Messages',
                        'method' => 'messages',
                        'logo' => 'img/mail.png',
                        'position' => 'left',
                        'configs' => array(
                                'count' => true
                        )
                ),
                'files' => array(
                        'name' => 'Files',
                        'method' => 'files',
                        'logo' => 'img/files.png',
                        'position' => 'left',
                        'configs' => array(
                                'count' => true
                        )
                ),
                'globals' => array(
                            'name' => 'Globals',
                            'method' => 'globals',
                            'logo' => 'img/globals.png',
                            'position' => 'left',
                            'configs' => array(
                                    'globals' => array(
                                            'post',
                                            'cookie',
                                            'session',
                                            'server',
                                            'request',
                                            'get',
                                    )
                            )
                    )
        ) 
);

