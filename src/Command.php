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

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

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
    
    private function genExcerpt($text, $term) {
        $excerpt = '';
        $textLength = strlen($text);
        $termLength = strlen($term);
        $termPosition = strpos($text, $term);

        if (!strstr($text, $term))
            return '';

        if ( ($termPosition > 20) && ( ($textLength - $termPosition - $termLength) > 20) ) {
            $start = $termPosition - 20;
            $length = 40 + $termLength;
            $truncated = substr($text, $start, $length );
            $excerpt = '...' . $truncated . '...';
        }

        else if ( ($termPosition < 20) && ( ($textLength - $termPosition - $termLength) > 20) ) {
            $start = 0;
            $length = $termPosition + ($termLength + 20); // 20 added with $termLength so that the search term is not included in '20 after'
            $truncated = substr($text, $start, $length );
            $excerpt = $truncated . '...';
        }

        else if ( ($termPosition > 20) && ( ($textLength - $termPosition - $termLength) < 20 ) ) {
            $start = $termPosition - 20;
            $length = 40 + $termLength;
            $truncated = substr($text, $start, $length );
            $excerpt = '...' . $truncated;
        }

        else if ( $textLength < (40 + $termLength) )
            $excerpt = $text;

        return $this->highlight($excerpt, $term);
    }

    private function highlight($excerpt, $term) 
    {
        $text = preg_filter('/' . preg_quote($term, '/') . '/i', '<red>$0</>', $excerpt);
        
        if (!empty($text)) {
            $excerpt = $text;
        }

        return $excerpt;
    }

    private function termNotFound()
    {
        $io->text('Term not found!');
        $io->newLine(2);
        exit();
    }
}