<?php

namespace App\Entity;

class Resume
{
    private static $RESUME_REQUIRED_FIELDS = ['profession', 'city', 'name', 'phone', 'email', 'birthday', 'educations', 'desiredSalary', 'skills', 'resumeStatus'];
    private static $EDUCATION_REQUIRED_FIELDS = ['educationLevel', 'educationPlace', 'educationFaculty', 'educationSpecialization', 'educationEndDate'];
    private static $EDUCATION_LEVELS = ['Среднее', 'Среднее специальное', 'Неоконченное высшее', 'Высшее'];
    private static $CORRECT_STATUSES = ['Новый', 'Назначено собеседование', 'Принят', 'Отказ'];

    // validate CV: check required fields and regexp
    public static function validateCV(array $resume): void
    {
        // check resume fields
        foreach (self::$RESUME_REQUIRED_FIELDS as $field) {
            // if education level is Среднее
            if (!isset($resume[$field]) || empty($resume[$field])) {
                throw new \Exception('Required field doesn\'t set');
            }
        }
        // check all educations
        foreach ($resume['educations'] as $education) {
            if (isset($education['educationLevel'])) {
                if ($education['educationLevel'] === self::$EDUCATION_LEVELS[0]) {
                    continue;
                }
            }
            // check education fields
            foreach (self::$EDUCATION_REQUIRED_FIELDS as $field) {
                if (!isset($education[$field]) || empty($education[$field])) {
                    throw new \Exception('Required field doesn\'t set');
                }
            }
            // check education level
            if (!in_array($education['educationLevel'], self::$EDUCATION_LEVELS, true)) {
                throw new \Exception('Incorrect education level');
            }
            // check education end date
            if (!preg_match("/^[0-9]+$/", $education['educationEndDate']) || (int)$education['educationEndDate'] < 1945 || (int)$education['educationEndDate'] > 2030) {
                throw new \Exception('Incorrect education end date');
            }
        }
        // check phone number
        if (!preg_match("/^[0-9]+$/", $resume['phone']) || strlen($resume['phone']) < 6 || strlen($resume['phone']) > 10) {
            throw new \Exception('Incorrect phone number');
        }
        // check desired salary
        if (!preg_match("/^[0-9]+$/", $resume['desiredSalary'])) {
            throw new \Exception('Incorrect desired salary');
        }
    }

    // validate new resume status
    public static function validateNewStatus(string $newStatus): bool
    {
        if (in_array($newStatus, self::$CORRECT_STATUSES, true)) {
            return true;
        }
        return false;
    }
}