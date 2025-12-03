<?php

require_once __DIR__ . '/../repository/DreamSymbolRepository.php';
require_once __DIR__ . '/DreamService.php';
require_once __DIR__ . '/../repository/DreamRepository.php';

class AnalyzeService {
    private $symbolRepository;
    private $dreamService;
    
    public function __construct($pdo) {
        $this->symbolRepository = new DreamSymbolRepository($pdo);
        $this->dreamService = new DreamService($pdo);
    }
    
    public function analyzeDream($dreamId, $userId, $dreamContent = null) {
        if ($dreamContent === null) {
            $dreamContent = $this->dreamService->getDreamContent($dreamId, $userId);
        }
        
        if (empty($dreamContent)) {
            throw new Exception('Dream content is required');
        }
        
        $symbols = $this->symbolRepository->findAll();
        
        if (empty($symbols)) {
            throw new Exception('No symbols found in database');
        }
        
        $foundSymbols = [];
        $contentLower = mb_strtolower(trim($dreamContent), 'UTF-8');
        
        foreach ($symbols as $symbol) {
            $word = $symbol['word'];
            $variantsStr = $symbol['variants'] ?? '';
            $meaning = $symbol['meaning'];
            
            $wordLower = mb_strtolower(trim($word), 'UTF-8');
            $found = false;
            
            $searchWords = [$wordLower];
            
            if (!empty($variantsStr)) {
                $variants = explode(',', $variantsStr);
                foreach ($variants as $variant) {
                    $variantLower = mb_strtolower(trim($variant), 'UTF-8');
                    if (!empty($variantLower) && mb_strlen($variantLower) > 0) {
                        $searchWords[] = $variantLower;
                    }
                }
            }
            
            foreach ($searchWords as $searchWord) {
                if (!empty($searchWord) && mb_strlen($searchWord) > 0) {
                    $pos = mb_strpos($contentLower, $searchWord, 0, 'UTF-8');
                    if ($pos !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            
            if ($found) {
                $foundSymbols[] = [
                    'word' => $word,
                    'meaning' => $meaning
                ];
            }
        }
        
        return $foundSymbols;
    }
}

