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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

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

        $io->newLine(2);
        $io->writeln([
            '<red>Reddit Search v0.1.0</>',
            '<red>====================</>'
        ]);

        $subreddit = $io->ask('Please enter the name of the subreddit (default: webdev): ', 'webdev');
        $term = $io->ask('Please enter a search term (default: php): ', 'php');

        $io->newLine();
        $io->text('Subreddit: ' . $subreddit);
        $io->text('Search term: ' . $term);
        $io->newLine();

        $url = 'https://www.reddit.com/r/' . $subreddit . '/new/.json?limit=100';

        $io->text('Searching for: ' . $term . ' at ' . $url . '...');
        $io->newLine();

        $data = $this->fetch($subreddit, $url);

        $table = new Table($output);
        $table->setHeaders(['Date', 'Title', 'URL', 'Excerpt']);
        $separator = new TableSeparator();

        $first = 0;
        foreach ($data->data->children as $item) {
            $title = $item->data->title;
            $text = $item->data->selftext;
            $postUrl = $item->data->url;
            $date = date('Y-m-d H:i:s', $item->data->created_utc);

            if (empty($text))
                continue;

            $excerpt = $this->genExcerpt($text, $term);

            if (empty($excerpt))
                continue;

            if (strlen($title) > 30)
                $title = substr($title,0,30).'...';

            $first++;
            if ($first === 1) {
                $table->addRow([$date, $title, $postUrl, $excerpt]);
            }
            else {
                $table->addRow($separator);
                $table->addRow([$date, $title, $postUrl, $excerpt]);
            }
        }
        $table->render();
    }

    private function fetch($subreddit, $url)
    {
        $client = new Client([
            'headers' => ['User-Agent' => 'redditsearch/1.0'],
            'verify' => false
        ]);

        // $response = $client->request("GET", '/r/' . $subreddit . '/search.json', ['term' => 'q=' . $term . '&sort=new' . '&restrict_sr=1&limit=10']);
        $response = $client->request("GET", 'https://www.reddit.com/r/' . $subreddit . '/new/.json?limit=100');

        $response = json_decode($response->getBody(true));

        if (empty($response->data->children))
            $this->subRedditNotFound();

        return $response;
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

    private function subRedditNotFound()
    {   
        exit('Subreddit Not Found!');
    }