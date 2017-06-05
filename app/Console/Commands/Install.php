<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Installer\helpdesk\InstallController;

class Install extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:faveo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to install faveo';
    protected $install;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        $this->install = new InstallController();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $this->appEnv();
            if ($this->confirm('Do you want to intall faveo?')) {
                $order_number = $this->ask('Enter your order number');
                $serial_key = $this->ask('Enter your serial key');
                $result = $this->install->billConnection($order_number, $serial_key);
                $response = $this->install->postSerialKey($result, false);
                if ($response == 'success') {
                    $default = $this->choice(
                            'Which sql engine would you like to use?', ['mysql']
                    );
                    $host = $this->ask('Enter your sql host');
                    $database = $this->ask('Enter your database name');
                    $dbusername = $this->ask('Enter your database username');
                    $dbpassword = $this->ask('Enter your database password (blank if not entered)', false);
                    $port = $this->ask('Enter your sql port (blank if not entered)', false);
                    $this->install->env($default, $host, $port, $database, $dbusername, $dbpassword);
                    $this->info('.env file has created');
                    $this->call('preinsatall:check');
                    $this->alert("please run 'php artisan install:db'");
                }
            } else {
                $this->info('We hope, you will try next time');
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }


    public function appEnv() {
        $extensions = [
            'curl',
            'ctype',
            'imap',
            'mbstring',
            'mcrypt',
            'mysql',
            'openssl',
            'tokenizer',
            'zip',
            'pdo',
            'mysqli',
            'bcmath',
            'iconv',
                //'ioncube_loader_dar_5.6',
        ];
        $result = [];
        foreach ($extensions as $key => $extension) {
            $result[$key]['extension'] = $extension;
            if (!extension_loaded($extension)) {
                $result[$key]['status'] = "Not Loading, Please open please open '" . php_ini_loaded_file() . "' and add 'extension = " . $extension;
            } else {
                $result[$key]['status'] = "Loading";
            }
        }
        $result['php']['extension'] = 'PHP';
        if (phpversion() >= 5.6) {
            $result['php']['status'] = "PHP version supports";
        } else {
            $result['php']['status'] = "PHP version doesn't supports please upgrade to 5.6+";
        }

        $headers = ['Extension', 'Status'];
        $this->table($headers, $result);
    }

    

}
