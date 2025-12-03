<?php

require_once __DIR__ . '/../repository/DreamRepository.php';

class DreamService {
    private $dreamRepository;
    
    public function __construct($pdo) {
        $this->dreamRepository = new DreamRepository($pdo);
    }
    
    public function validateDream($data) {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }
        
        if (empty($data['dream_date'])) {
            $errors[] = 'Dream date is required';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $data['dream_date']);
            if (!$date || $date->format('Y-m-d') !== $data['dream_date']) {
                $errors[] = 'Invalid date format. Use YYYY-MM-DD';
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
            $updateData['title'] = trim($data['title']);
        }
        
        if (isset($data['content'])) {
            $updateData['content'] = trim($data['content']);
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

