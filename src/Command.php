<?php 
namespace Osky;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Command extends SymfonyCommand
{
    
    public function __construct()
    {
        parent::__construct();
    }

    protected function search(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $outputStyle = new OutputFormatterStyle('red');
        $io->getFormatter()->setStyle('red', $outputStyle);

        $io->title('<red>Reddit Search</>');

        $subreddit = $io->ask('Please enter the name of the subreddit (default: webdev): ', 'webdev');
        $query = $io->ask('Please enter a search term (default: php): ', 'php');

        $io->newLine();
        $io->text('Subreddit: ' . $subreddit);
        $io->text('Search term: ' . $query);
        $io->newLine();
    }

    private function fetch($subreddit)
    {
        $client = new Client([
            'base_uri' => 'https://www.reddit.com',
            'headers' => ['User-Agent' => 'reddtsearch/1.0'],
            'verify' => false
        ]);

        // $response = $client->request("GET", '/r/' . $subreddit . '/search.json', ['query' => 'q=' . $query . '&sort=new' . '&restrict_sr=1&limit=10']);
        $response = $client->request("GET", 'https://www.reddit.com/r/redditdev/new/.json?limit=100');

        $body = json_decode($response->getBody(true));

        return $body;
    }
}