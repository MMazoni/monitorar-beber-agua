<?php

namespace BeberAgua\API\Domain\Helper;

class Hydrate
{
    public static function hydrateHistoryList(\PDOStatement $stmt): array
    {
        $historyDataList = $stmt->fetchAll();
        $historyList = [];

        foreach ($historyDataList as $historyData) {
            $historyList[] = array(
                "date" => $historyData['drink_datetime'],
                "water_ml" => $historyData['drink_ml'],
            );
        }
        return $historyList;
    }

    public static function hydrateRankingList(\PDOStatement $stmt): array
    {
        $rankingDataList = $stmt->fetchAll();
        $rankingList = [];

        foreach ($rankingDataList as $rankingData) {
            $rankingList[] = array(
                "name" => $rankingData['name'],
                "water_ml" => number_format($rankingData['SUM(d.drink_ml)'], 2, ",", "."),
            );
        }
        return $rankingList;
    }

    public static function hydrateUserList(\PDOStatement $stmt): array
    {
        $userDataList = $stmt->fetchAll();
        $userList = [];

        foreach ($userDataList as $userData) {
            $userList[] = array(
                "user_id" => $userData['id_user'],
                "email" => $userData['email'],
                "name" => $userData['name'],
                "drink_counter" => $userData['drink_counter'],
            );
        }
        return $userList;
    }
}
