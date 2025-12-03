<?php

require_once __DIR__ . '/../repository/DreamRepository.php';

class StatsService {
    private $dreamRepository;
    private $pdo;
    
    public function __construct($pdo) {
        $this->dreamRepository = new DreamRepository($pdo);
        $this->pdo = $pdo;
    }
    
    public function getStats($userId) {
        $dreams = $this->dreamRepository->findByUserId($userId);
        
        $totalDreams = count($dreams);
        
        $moodDistribution = [];
        foreach ($dreams as $dream) {
            $mood = $dream['mood'] ?? 'Не указано';
            if (!isset($moodDistribution[$mood])) {
                $moodDistribution[$mood] = 0;
            }
            $moodDistribution[$mood]++;
        }
        
        arsort($moodDistribution);
        $moodDistributionArray = [];
        foreach ($moodDistribution as $mood => $count) {
            $moodDistributionArray[] = [
                'mood' => $mood,
                'count' => $count
            ];
        }
        
        $wordFrequency = [];
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'is', 'was', 'are', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'this', 'that', 'these', 'those', 'am', 'me', 'as', 'up', 'so', 'if', 'no', 'not', 'out', 'than', 'then', 'there', 'when', 'where', 'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more', 'most', 'other', 'some', 'such',
            'что', 'это', 'как', 'так', 'его', 'этот', 'она', 'эта', 'они', 'был', 'была', 'было', 'были', 'будет', 'есть', 'меня', 'мне', 'мной', 'тебя', 'тебе', 'тобой', 'него', 'нему', 'ним', 'который', 'которая', 'которое', 'которые', 'весь', 'вся', 'все', 'всё', 'один', 'одна', 'одно', 'при', 'без', 'для', 'под', 'над', 'того', 'тому', 'чтобы', 'если', 'когда', 'потом', 'где', 'куда', 'откуда', 'почему', 'зачем', 'себя', 'собой', 'свой', 'свою', 'свои', 'этого', 'этому', 'этой', 'этим', 'была', 'были', 'было', 'будет', 'буду', 'будешь', 'будут', 'есть', 'была', 'были', 'быть'
        ];
        
        foreach ($dreams as $dream) {
            $content = strtolower($dream['content']);
            $content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content);
            $words = preg_split('/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($words as $word) {
                if (strlen($word) >= 5 && !in_array($word, $stopWords)) {
                    if (!isset($wordFrequency[$word])) {
                        $wordFrequency[$word] = 0;
                    }
                    $wordFrequency[$word]++;
                }
            }
        }
        
        arsort($wordFrequency);
        $topWords = array_slice(array_keys($wordFrequency), 0, 3);
        $topWordsWithCount = [];
        foreach ($topWords as $word) {
            $topWordsWithCount[] = [
                'word' => $word,
                'count' => $wordFrequency[$word]
            ];
        }
        
        $stmt = $this->pdo->prepare('SELECT id, title, dream_date FROM dreams WHERE user_id = ? ORDER BY dream_date DESC, created_at DESC LIMIT 5');
        $stmt->execute([$userId]);
        $recentDreams = $stmt->fetchAll();
        
        return [
            'total_dreams' => $totalDreams,
            'mood_distribution' => $moodDistributionArray,
            'top_words' => $topWordsWithCount,
            'recent_dreams' => $recentDreams
        ];
    }
}

