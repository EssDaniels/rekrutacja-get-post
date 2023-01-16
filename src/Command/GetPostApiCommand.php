<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\PostRepository;
use GuzzleHttp\Client;
use App\Entity\Post;

#[AsCommand(
    name: 'app:get-post-api',
    description: 'Pobieranie postów za pomocą API. Posty są sprawdzane po tytule czy już dany post istniej, jeśli dny tytuł się powtarza jest ignorowany.',
)]
class GetPostApiCommand extends Command
{
    public function __construct(
        private PostRepository $posts
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);
        // Pobranie danych z API
        $client = new Client();
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $posts = json_decode($response->getBody()->getContents(), true);

        // Pobranie imienia i nazwiska autora z API
        $users = array();
        foreach ($posts as $post) {
            $userId = $post['userId'];
            if (!isset($users[$userId])) {
                $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/users/' . $userId);
                $users[$userId] = json_decode($response->getBody()->getContents(), true);
            }
        }

        // Zapis danych do bazy danych
        $newPost = null;
        foreach ($posts as $post) {
            !$existingPost = $this->posts->findOneBy(['title' => $post['title']]);
            if (!$existingPost) {
                $newPost = new Post();
                $newPost->setTitle($post['title']);
                $newPost->setBody($post['body']);
                $newPost->setAuthorName($users[$post['userId']]['name']);
                $this->posts->save($newPost, false);
            }
        }
        if (!empty($newPost)) {
            $this->posts->save($newPost, true);
            $output->success('Posty zostały pobrane i zapisane do bazy danych');
        } else {
            $output->warning('Brak nowych postów');
        }
        return Command::SUCCESS;
    }
}
