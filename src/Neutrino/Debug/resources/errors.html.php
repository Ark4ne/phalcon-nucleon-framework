<?php
$macro = [];
if (!isset($macro['renderFileInfo'])) {
    $macro['renderFileInfo'] = function ($elem) use (&$macro) {
        if (isset($elem['file'], $elem['line'])) : ?>
          <ul class="collapsible">
            <li>
              <div class="collapsible-header">
                <small class="grey-text text-darken-3" title="View code">in
                  : <?= Neutrino\Debug\file_highlight($elem['file']) ?>&nbsp;(line: <?= $elem['line'] ?>)
                </small>
              </div>
              <div class="collapsible-body">
                  <?= Neutrino\Debug\php_file_part_highlight($elem['file'], $elem['line']); ?>
              </div>
            </li>
          </ul>
        <?php elseif (isset($elem['file'])) : ?>
          <small class="grey-text text-darken-3">in : <?= Neutrino\Debug\file_highlight($elem['file']) ?></small>
        <?php else : ?>
          <small class="grey-text text-darken-3">[internal function]</small>
        <?php endif;
    };
}
if (!isset($macro['renderTrace'])) {
    $macro['renderTrace'] = function ($exception) use (&$macro) {
        if (empty($exception['traces'])) {
            return;
        }
        ?>
      <ul class="collection">
          <?php foreach ($exception['traces'] as $trace) : ?>
            <li class="collection-item blue-grey lighten-3 white-text">
              <span class="grey-text text-darken-3"><?= Neutrino\Debug\func_highlight($trace['func']) ?></span>
              <br/>
                <?= $macro['renderFileInfo']($trace) ?>
            </li>
          <?php endforeach; ?>
      </ul>
        <?php
    };
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <title>Error</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,300,400"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <style rel="stylesheet">
.small{font-size:75%}pre.sql{white-space: pre-line; word-break: break-all; font-size: 13px !important;margin:0}pre.sql .string{color:#a5d6a7 !important}pre.sql .table{color:#90caf9 !important}pre.sql .column{color:#ce93d8 !important}pre.sql .func{color:#fdd835 !important}pre.sql .keyw{color:#fb8c00 !important}.php-error{padding:10px 15px;margin-bottom:10px}.php-error.debug{background-color:#4db6ac !important;color:#212121 !important}.php-error.info{background-color:#fff176 !important;color:#212121 !important}.php-error.notice{background-color:#ffd54f !important;color:#212121 !important}.php-error.warning{background-color:#ff8a65 !important;color:#212121 !important}.php-error.error{background-color:#b71c1c !important;color:#f5f5f5 !important}.php-error .type,.php-error .msg{font-family:monospace, monospace}.php-error .msg{margin:3px 0;word-break:break-all;white-space:pre-line}.php-error .file{font-size:80%}.collapsible{-webkit-box-shadow:none;box-shadow:none;border:none;margin:0}.collapsible-header,.collapsible-body{background:transparent;padding:.25rem 0;border:none;color:#424242}.collapsible-header:hover{text-decoration:underline}.collection{word-break:break-all;word-wrap:break-word}pre.pre-block{word-break:break-all;max-width:100%;margin:0;overflow:hidden;white-space:pre-line;}
  </style>
</head>
<body class="grey darken-3 grey-text text-lighten-3">

<div class="row">
  <div class="col s12">
    <ul class="tabs grey darken-4">
      <li class="tab col s3">
        <a class="active" href="#error">
            <?php if ($isException) : ?>
              Exception<?= (Neutrino\Debug\length($exceptions) > 1 ? 's' : '') ?>
              <span class="chip"><?= Neutrino\Debug\length($exceptions) ?></span>
            <?php else : ?>
              Fatal error
            <?php endif; ?>
        </a>
      </li>
        <?php if (isset($php_errors)) : ?>
          <li class="tab col s3 <?= (empty(Neutrino\Debug\length($php_errors)) ? 'disabled' : '') ?>">
            <a href="#php-errors">Errors <span class="chip"><?= Neutrino\Debug\length($php_errors) ?></span></a>
          </li>
        <?php endif; ?>
        <?php if (!empty($profilers)) : ?>
          <li class="tab col s3 <?= (empty(Neutrino\Debug\length($profilers)) ? 'disabled' : '') ?>">
            <a href="#profilers">Profilers <span class="chip"><?= Neutrino\Debug\length($profilers) ?></span></a>
          </li>
        <?php endif; ?>
        <?php if (isset($events)) : ?>
          <li class="tab col s3 <?= (empty(Neutrino\Debug\length($events)) ? 'disabled' : '') ?>">
            <a href="#events">Events <span class="chip"><?= Neutrino\Debug\length($events) ?></span></a>
          </li>
        <?php endif; ?>
    </ul>
  </div>
  <div id="error" class="col s12">
      <?php if ($error['isException']) : ?>
          <?php $index = 0 ?>
          <?php foreach ($exceptions as $exception) : $index++; ?>
          <div class="card grey lighten-3">
            <div class="card-content">
              <span class="card-title red-text text-accent-4">
                #<?= $index ?> <span title="Exception code [<?= $exception['code'] ?>]"><b><?= $exception['class'] ?></b> <code class="small">[<?= $exception['code'] ?>]</code></span>
                <br/>
                <?= $macro['renderFileInfo']($exception) ?>
                <pre class="pre-block grey-text text-darken-3 small"><?=
                    htmlspecialchars(empty($error['message']) ? 'no message' : $error['message'])
                    ?></pre>
              </span>
              <div>
                  <?= $macro['renderTrace']($exception) ?>
              </div>
            </div>
          </div>
      <?php endforeach; ?>
      <?php else : ?>
        <div class="card grey lighten-3">
          <div class="card-content">
            <span class="card-title red-text text-accent-4">
                <span title="Error code [<?= $error['code'] ?>]"><b><?= $error['typeStr'] ?></b> <code class="small">[<?= $error['code'] ?>]</code></span>
              <br/>
              <?= $macro['renderFileInfo']($exception) ?>
              <pre class="pre-block grey-text text-darken-3 small"><?=
                  htmlspecialchars(empty($error['message']) ? 'no message' : $error['message'])
                  ?></pre>
            </span>
            <div>
                <?= $macro['renderTrace']($error) ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
  </div>
    <?php if (isset($php_errors)) : ?>
      <div id="php-errors" class="col s12">
        <div class="card grey darken-4">
          <div class="card-content">
            <div style="margin: 0;padding: 0;">
                <?php foreach ($php_errors as $error) : ?>
                    <?php if ($error['logLvl'] === \Phalcon\Logger::DEBUG) : ?>
                        <?php $color = 'debug'; ?>
                    <?php elseif ($error['logLvl'] === \Phalcon\Logger::INFO) : ?>
                        <?php $color = 'info'; ?>
                    <?php elseif ($error['logLvl'] === \Phalcon\Logger::NOTICE) : ?>
                        <?php $color = 'notice'; ?>
                    <?php elseif ($error['logLvl'] === \Phalcon\Logger::WARNING) : ?>
                        <?php $color = 'warning'; ?>
                    <?php else : ?>
                        <?php $color = 'error'; ?>
                    <?php endif; ?>
                  <div class="php-error <?= $color ?>">
                    <span class="type"><?= $error['typeStr'] ?></span> :
                    <pre class="pre-block"><?= htmlspecialchars(empty($error['message']) ? 'no message' : $error['message']) ?></pre>
                    <?= $macro['renderFileInfo']($error) ?>
                  </div>
                <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($profilers)) : ?>
      <div id="profilers" class="col s12">
        <div class="card grey darken-4">
          <div class="col s12">
            <ul class="tabs grey darken-4">
                <?php foreach ($profilers as $name => $elements) : ?>
                    <?php $profiler = $elements['profiler']; ?>
                    <?php $profiles = (empty($profiler->getProfiles()) ? ([]) : ($profiler->getProfiles())); ?>
                  <li class="tab col s3">
                    <a href="#profilers-<?= $name ?>"><?= $name ?> <span class="chip"><?= Neutrino\Debug\length($profiles) ?></span> </a>
                  </li>
                <?php endforeach; ?>
            </ul>
          </div>
            <?php foreach ($profilers as $name => $elements) : ?>
                <?php $profiler = $elements['profiler']; ?>
                <?php $profiles = (empty($profiler->getProfiles()) ? ([]) : ($profiler->getProfiles())); ?>
              <div id="profilers-<?= $name ?>">
                <table style="margin: 0;padding: 0;" class="bordered">
                  <thead>
                  <tr class="grey darken-4">
                    <th style="padding: 5px 10px;border-radius: 0">-</th>
                    <th style="padding: 5px 10px;border-radius: 0">request</th>
                    <th style="padding: 5px 10px;border-radius: 0">vars</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($profiles as $profile) : ?>
                    <tr class="grey darken-4">
                      <td style="padding: 5px 10px;border-radius: 0">
                        <small style="white-space: nowrap;"><?= Neutrino\Debug\human_mtime($profile->getTotalElapsedSeconds()) ?></small>
                      </td>
                      <td style="padding: 5px 10px;border-radius: 0">
                        <pre class="sql"><?= Neutrino\Debug\sql_highlight($profile->getSqlStatement()) ?></pre>
                      </td>
                      <td style="padding: 5px 10px;border-radius: 0">
                          <?php $vars = $profile->getSqlVariables(); ?>
                          <?php if ($vars != null) : ?>
                              <?php foreach ($vars as $var => $value) : ?>
                              <pre>:<?= $var ?> = <?= $value ?></pre>
                              <?php endforeach; ?>
                          <?php else : ?>
                            --
                          <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if (isset($events)) : ?>
      <div id="events" class="col s12">
        <div class="card grey darken-4">
          <div class="card-content">
            <table style="margin: 0;padding: 0;" class="bordered">
                <?php $mt_start = $_SERVER['REQUEST_TIME_FLOAT']; ?>
              <thead>
              <tr>
                <th>-</th>
                <th>type</th>
                <th>src</th>
                <th>data</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td style="padding: 5px 10px">
                  <small>0 ns</small>
                </td>
                <td style="padding: 5px 10px">
                  <small class="event">
                    REQUEST_TIME_FLOAT
                  </small>
                </td>
                <td style="padding: 5px 10px">
                </td>
                <td style="padding: 5px 10px">
                </td>
              </tr>
              <?php foreach ((empty($events) ? ([]) : ($events)) as $event) : ?>
                <tr class="grey darken-4" style="padding: 5px 10px">
                  <td style="padding: 5px 10px;border-radius: 0">
                    <small style="white-space: nowrap;"><?= Neutrino\Debug\human_mtime(($event['mt'] - $mt_start)) ?></small>
                  </td>
                  <td style="padding: 5px 10px;border-radius: 0">
                    <small style="white-space: nowrap;">
                      <span class="blue-text text-lighten-3"><?= $event['space'] ?></span>:<span class="purple-text text-lighten-3"><?= $event['type'] ?></span>
                    </small>
                  </td>
                  <td style="padding: 5px 10px;border-radius: 0">
                    <small><?= $event['src'] ?></small>
                  </td>
                  <td style="padding: 5px 10px;border-radius: 0">
                    <small title="<?= (is_string($event['raw_data']) ? $event['raw_data'] : '') ?>"><?= $event['data'] ?></small>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
</body>
</html>
