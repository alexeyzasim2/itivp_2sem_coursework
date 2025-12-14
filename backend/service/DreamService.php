<?php

require_once __DIR__ . '/../repository/DreamRepository.php';

class DreamService {
    private $dreamRepository;
    
    public function __construct($pdo) {
        $this->dreamRepository = new DreamRepository($pdo);
    }
    
    public function validateDream($data) {
        $errors = [];
        
        $title = isset($data['title']) ? trim($data['title']) : '';
        $content = isset($data['content']) ? trim($data['content']) : '';
        $dreamDate = $data['dream_date'] ?? null;
        
        if ($title === '') {
            $errors[] = 'Title is required';
        }
        
        if ($content === '') {
            $errors[] = 'Content is required';
        }
        
        if (empty($dreamDate)) {
            $errors[] = 'Dream date is required';
        }
        
        if ($title !== '' && mb_strlen($title) > 255) {
            $errors[] = 'Title must not exceed 255 characters';
        }
        
        if ($content !== '' && mb_strlen($content) > 1000) {
            $errors[] = 'Content must not exceed 1000 characters';
        }
            
        if (!empty($dreamDate)) {
            $date = DateTime::createFromFormat('Y-m-d', $dreamDate);
            if (!$date || $date->format('Y-m-d') !== $dreamDate) {
                $errors[] = 'Invalid date format. Use YYYY-MM-DD';
            } else {
                $today = new DateTime('today');
                $hundredYearsAgo = (clone $today)->modify('-100 years');
                
                if ($date > $today) {
                    $errors[] = 'Dream date cannot be in the future';
                }
                
                if ($date < $hundredYearsAgo) {
                    $errors[] = 'Dream date cannot be more than 100 years ago';
                }
            }
        }
        
        return $errors;
    }
    
    public function createDream($userId, $data) {
        $errors = $this->validateDream($data);
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $title = trim($data['title']);
        $content = trim($data['content']);
        $mood = isset($data['mood']) ? trim($data['mood']) : '';
        $dreamDate = $data['dream_date'];
        
        $dreamId = $this->dreamRepository->create($userId, $title, $content, $mood, $dreamDate);
        
        return $this->dreamRepository->findById($dreamId, $userId);
    }
    
    public function updateDream($dreamId, $userId, $data) {
        $dream = $this->dreamRepository->findById($dreamId, $userId);
        
        if (!$dream) {
            throw new Exception('Dream not found');
        }
        
        $updateData = [];
        
        if (isset($data['title'])) {
            $title = trim($data['title']);
            if ($title === '') {
                throw new Exception('Title is required');
            }
            if (mb_strlen($title) > 255) {
                throw new Exception('Title must not exceed 255 characters');
            }
            $updateData['title'] = $title;
        }
        
        if (isset($data['content'])) {
            $content = trim($data['content']);
            if ($content === '') {
                throw new Exception('Content is required');
            }
            if (mb_strlen($content) > 1000) {
                throw new Exception('Content must not exceed 1000 characters');
            }
            $updateData['content'] = $content;
        }
        
        if (isset($data['mood'])) {
            $updateData['mood'] = trim($data['mood']);
        }
        
        if (isset($data['dream_date'])) {
            $dreamDate = $data['dream_date'];
            $date = DateTime::createFromFormat('Y-m-d', $dreamDate);
            if (!$date || $date->format('Y-m-d') !== $dreamDate) {
                throw new Exception('Invalid date format. Use YYYY-MM-DD');
            }
            $updateData['dream_date'] = $dreamDate;
        }
        
        if (empty($updateData)) {
            throw new Exception('No fields to update');
        }
        
        $this->dreamRepository->update($dreamId, $userId, $updateData);
        
        return $this->dreamRepository->findById($dreamId, $userId);
    }
    
    public function deleteDream($dreamId, $userId) {
        $dream = $this->dreamRepository->findById($dreamId, $userId);
        
        if (!$dream) {
            throw new Exception('Dream not found');
        }
        
        return $this->dreamRepository->delete($dreamId, $userId);
    }
    
    public function getDreamsByUserId($userId) {
        return $this->dreamRepository->findByUserId($userId);
    }
    
    public function getDreamById($dreamId, $userId) {
        $dream = $this->dreamRepository->findById($dreamId, $userId);
        
        if (!$dream) {
            throw new Exception('Dream not found');
        }
        
        return $dream;
    }
    
    public function getDreamContent($dreamId, $userId) {
        $dream = $this->dreamRepository->getContentById($dreamId, $userId);
        
        if (!$dream) {
            throw new Exception('Dream not found');
        }
        
        $content = $dream['content'];
        if (empty($content)) {
            $content = $dream['title'] ?? '';
        }
        
        return $content;
    }
}

