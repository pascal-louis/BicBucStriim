<?php

use App\Domain\BicBucStriim\BicBucStriim;
use App\Domain\BicBucStriim\AppConstants;
use App\Domain\Calibre\Calibre;
use Slim\Views\Twig;

// DIC configuration
$container = $app->getContainer();

// TODO Enable Slim http cache
/*$container['cache'] = function ($c) {
    return new \Slim\HttpCache\CacheProvider();
};*/

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Twig
$container['view'] = function ($c) {
    // TODO provide dir var/cache
    return Twig::create('templates', ['cache' => '../var/cache']);
};

// bicbucstriim
$container['bbs'] = function ($c) {
    $settings = $c->get('settings')['bbs'];
    $logger = $c->get('logger');
    $dbd = $settings['dataDb'];
    $public = $settings['public'];
    $logger->debug("using bbs db $dbd and public path $public");
    $bbs = new BicBucStriim($dbd, true);
    if (!$bbs->dbOk()) {
        $bbs->createDataDb($dbd);
        $bbs = new BicBucStriim($dbd, true);
    }
    return $bbs;
};

// user configuration
$container['config'] = function ($c) {
    $configs = array(
        AppConstants::CALIBRE_DIR => '',
        AppConstants::DB_VERSION => AppConstants::DB_SCHEMA_VERSION,
        AppConstants::KINDLE => 0,
        AppConstants::KINDLE_FROM_EMAIL => '',
        AppConstants::THUMB_GEN_CLIPPED => 1,
        AppConstants::PAGE_SIZE => 30,
        AppConstants::DISPLAY_APP_NAME => 'BicBucStriim',
        AppConstants::MAILER => Mailer::MAIL,
        AppConstants::SMTP_USER => '',
        AppConstants::SMTP_PASSWORD => '',
        AppConstants::SMTP_SERVER => '',
        AppConstants::SMTP_PORT => 25,
        AppConstants::SMTP_ENCRYPTION => 0,
        AppConstants::METADATA_UPDATE => 0,
        AppConstants::LOGIN_REQUIRED => 1,
        AppConstants::TITLE_TIME_SORT => AppConstants::TITLE_TIME_SORT_TIMESTAMP,
        AppConstants::RELATIVE_URLS => 1,
    );
    $bbs = $c->get('bbs');
    $logger = $c->get('logger');
    if (!is_null($bbs) && $bbs->dbOk()) {
        $logger->debug("loading configuration");
        $css = $bbs->configs();
        foreach ($css as $cs) {
            if (array_key_exists($cs->name, $configs)) {
                $logger->debug("configuring value {$cs->val} for {$cs->name}");
                $configs[$cs->name] = $cs->val;
            } else {
                $logger->warn("ignoring unknown configuration, name: {$cs->name}, value: {$cs->val}");
            }
        }
    } else {
        $logger->debug("no configuration loaded");
    }
    return $configs;
};


// calibre
$container['calibre'] = function ($c) {
    $cdir = $c->get('config')[AppConstants::CALIBRE_DIR];
    $logger = $c->get('logger');
    if (!empty($cdir)) {
        try {
            $calibre = new Calibre($cdir . '/metadata.db');
        } catch (PDOException $ex) {
            $logger->error("Error opening Calibre library: " . var_export($ex, true));
            return null;
        }
        if ($calibre->libraryOk()) {
            $logger->debug('Calibre library ok');
        } else {
            $calibre = null;
            $logger->error(getcwd());
            $logger->error("Unable to open Calibre library at " . realpath($cdir));
        }
    } else {
        $logger->debug('No Calibre library');
        $calibre = null;
    }
    return $calibre;
};

// l10n

$container['l10n'] = function ($c) {
    return new \App\Domain\BicBucStriim\L10n();
};
