<?php

  require __DIR__ . '/../../vendor/autoload.php';

  $mustache = new \Phly\Mustache\Mustache();
  $mustache
    ->setTemplatePath(__DIR__)
    ->setSuffix('html');

  // ############################################

  if(! isset($_GET['content']))
  {
    $_GET['content'] = '01';
  }

  // ############################################

  if($_GET['content'] == '01')
  {
    $data = [
      'name'     => 'Tino',
      'comments' => [
        'items' => [
          [
            'id'      => 1,
            'user'    => 'Tino',
            'message' => 'Hello World',
            'created' => time(),
          ],
          [
            'id'      => 2,
            'user'    => 'Hans',
            'message' => 'Die dicke Berta!',
            'created' => time(),
          ],
          [
            'id'      => 3,
            'user'    => 'Manu',
            'message' => 'What goes around that comes around',
            'created' => time(),
          ],
        ]
      ]
    ];

    $partials = [
      'content' => 'content01',
    ];
  }

  // ############################################

  if($_GET['content'] == '02')
  {
    $data = [
      'fullName'     => 'Tino Ehrich',
    ];

    $partials = [
      'content' => 'content02',
    ];
  }

  // ############################################

  echo $mustache->render('base', $data, $partials);