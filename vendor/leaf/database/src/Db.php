<?php

/**
 * Класс фабрика, для более быстрого и структурированого
 * доступа к классам-помощникам базы данных.
 *
 *   Короткая версия  |      Вернет обьект
 * -------------------|-------------------------
 *   [`DB::query()`]  |   [Parametrized_Query]
 *   [`DB::inst()`]   |    [Driver_Database]
 *
 * @package Database
 * @author iToktor
 * @since 1.1.5
 */
class Db {
    
    /**
     * Короткая версия параметризованого запроса.
     * 
     * @param string $sql
     * @return Parameterized_Query
     */
    public static function query($sql) {
        return new Parameterized_Query($sql);
    }
    
    /**
     * Короткая версия инициализации бд.
     * 
     * @param string $name - имя экземпляра.
     * @param array $config - массив настроек бд.
     * @return Driver_Database
     */
    public static function inst($name = NULL, array $config = NULL) {
        Driver_Database::instance($name, $config);
    }
}
