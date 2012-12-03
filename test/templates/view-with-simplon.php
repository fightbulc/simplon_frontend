<?php

  require __DIR__ . '/../../vendor/autoload.php';

  $template = (new \Simplon\Frontend\Template())
    ->setTemplatePath(__DIR__)
    ->setTemplateSuffix('html');

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

    $template->setDataItems($data);

    $template
      ->addPartialAlias('content', 'content01')
      ->addPartialAlias('sidebar', 'sidebar01');
  }

  // ############################################

  if($_GET['content'] == '02')
  {
    $template->addDataItem('fullName', 'Tino Ehrich');

    $template
      ->addPartialAlias('content', 'content02')
      ->addPartialAlias('sidebar', 'sidebar02');
  }

  // ############################################

  echo $template
    ->setTemplate('base')
    ->render();