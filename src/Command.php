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
}