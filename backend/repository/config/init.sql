CREATE DATABASE IF NOT EXISTS dreamjournal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dreamjournal;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dreams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50),
    dream_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_dream_date (dream_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dream_symbols (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    variants TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    meaning TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    INDEX idx_word (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dream_symbols_found (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dream_id INT NOT NULL,
    symbol_id INT NOT NULL,
    found_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dream_id) REFERENCES dreams(id) ON DELETE CASCADE,
    FOREIGN KEY (symbol_id) REFERENCES dream_symbols(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dream_symbol (dream_id, symbol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO dream_symbols (word, variants, meaning) VALUES
('вода', 'воды,воде,воду,водой,водою,вод,водам,водами,водах', 'Вода символизирует эмоции и чувства. Чистая вода - к спокойствию, мутная - к тревогам.'),
('огонь', 'огня,огню,огонь,огнем,огне,огни,огням,огнями,огнях', 'Огонь означает страсть, энергию или гнев. Контролируемый огонь - к успеху, неконтролируемый - к конфликтам.'),
('змея', 'змеи,змее,змею,змеей,змеею,змей,змеям,змеями,змеях', 'Змея может означать мудрость, трансформацию или скрытую угрозу.'),
('смерть', 'смерти,смертью,смертей,смертям,смертями,смертях', 'Смерть во сне часто символизирует конец чего-то старого и начало нового.'),
('полет', 'полета,полету,полетом,полете,полеты,полетам,полетами,полетах', 'Полет означает свободу, стремление к достижению целей или желание уйти от проблем.'),
('падение', 'падения,падению,падением,падении,падений,падениям,падениями,падениях', 'Падение может означать потерю контроля, страх неудачи или необходимость быть более осторожным.'),
('дом', 'дома,дому,домом,доме,дома,домам,домами,домах', 'Дом символизирует вашу личность или внутренний мир. Разные комнаты - разные аспекты жизни.'),
('дорога', 'дороги,дороге,дорогу,дорогой,дорогою,дорог,дорогам,дорогами,дорогах', 'Дорога означает жизненный путь. Прямая дорога - к ясности, извилистая - к неопределенности.'),
('деньги', 'денег,деньгам,деньгами,деньгах', 'Деньги во сне могут означать ценность, успех или беспокойство о материальных вопросах.'),
('ребенок', 'ребенка,ребенку,ребенком,ребенке,дети,детей,детям,детьми,детях', 'Ребенок символизирует новые начинания, невинность или внутреннего ребенка.'),
('животное', 'животного,животному,животным,животном,животные,животных,животным,животными', 'Животные часто представляют инстинкты или скрытые аспекты личности.'),
('лес', 'леса,лесу,лесом,лесе,леса,лесам,лесами,лесах', 'Лес означает неизвестность, поиск или необходимость найти свой путь.'),
('море', 'моря,морю,морем,море,моря,морям,морями,морях', 'Море символизирует бессознательное, эмоции или бесконечные возможности.'),
('гора', 'горы,горе,гору,горой,горами,горах', 'Гора означает препятствия, которые нужно преодолеть, или достижение высоких целей.'),
('дождь', 'дождя,дождю,дождем,дожде,дожди,дождям,дождями,дождях', 'Дождь может означать очищение, грусть или обновление.'),
('солнце', 'солнца,солнцу,солнцем,солнце,солнца,солнцам,солнцами,солнцах', 'Солнце символизирует радость, успех, просветление или позитивную энергию.'),
('луна', 'луны,луне,луну,луной,луною,луны,лунам,лунами,лунах', 'Луна означает интуицию, женственность или скрытые эмоции.'),
('кошка', 'кошки,кошке,кошку,кошкой,кошкою,кошки,кошкам,кошками,кошках', 'Кошка символизирует независимость, тайну или женскую энергию.'),
('собака', 'собаки,собаке,собаку,собакой,собакою,собаки,собакам,собаками,собаках', 'Собака означает верность, дружбу или потребность в защите.'),
('птица', 'птицы,птице,птицу,птицей,птицею,птицы,птиц,птицам,птицами,птицах', 'Птица символизирует свободу, духовность или желание улететь от проблем.');

INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$qIhZHPS2gqA943X3lKECT.yxKbXzCfgFstL4PVgVmPeXUw1BGAiPG', 'admin');

