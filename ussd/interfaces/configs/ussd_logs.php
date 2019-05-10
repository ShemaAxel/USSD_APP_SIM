<?php

return array(
    'rootLogger' => array(
        'appenders' => array('debug'),
    ),
    'appenders' => array(
        'info' => array(
            'class' => 'LoggerAppenderFile',
            'layout' => array(
                'class' => 'LoggerLayoutSimple'
            ),
            'params' => array(
                'file' => '/var/log/applications/rw/kwigira/ussd/INFO.log',
                'append' => true
            )
        ),
        'error' => array(
            'class' => 'LoggerAppenderFile',
            'layout' => array(
                'class' => 'LoggerLayoutSimple'
            ),
            'params' => array(
                'file' => '/var/log/applications/rw/kwigira/ussd/ERROR.log',
                'append' => true
            )
        ),
        'debug' => array(
            'class' => 'LoggerAppenderFile',
            'layout' => array(
                'class' => 'LoggerLayoutSimple'
            ),
            'params' => array(
                'file' => '/var/log/applications/rw/kwigira/ussd/DEBUG.log',
                'append' => true
            )
        ),
        'fatal' => array(
            'class' => 'LoggerAppenderFile',
            'layout' => array(
                'class' => 'LoggerLayoutSimple'
            ),
            'params' => array(
                'file' => '/var/log/applications/rw/kwigira/ussd/FATAL.log',
                'append' => true
            )
        )
    )
);

