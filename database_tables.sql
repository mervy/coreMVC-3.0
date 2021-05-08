-- --------------------------------------------------------

--
-- Estrutura da tabela `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `status` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not' COMMENT 'yes or not',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `authors_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `authors`
--

CREATE TABLE IF NOT EXISTS `authors` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nickname` varchar(32) COLLATE utf8_unicode_ci UNIQUE KEY NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci UNIQUE KEY NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `status` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes' COMMENT 'yes or not'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `authors`
-- Senha/Pass: 12345

INSERT INTO `authors` (`id`, `nickname`, `name`, `email`, `password`, `status`) VALUES
(1, 'Admin', 'Administrador', 'admin@admin.com.br', '$2y$10$56P7Z4K2uflJOY.Emaep2.DknExvDZS4BYlQZWN8zrtbZF4K8qVb2', 'yes');

-- --------------------------------------------------------

--
-- Estrutura da tabela `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci UNIQUE KEY NOT NULL,
  `slug` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci UNIQUE KEY NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `registered_in` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `visitors`
--

CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `articles_id` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `acessed_in` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD KEY `fk_articles_authors_idx` (`authors_id`), ADD KEY `fk_articles_categories1_idx` (`categories_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD KEY `fk_visitors_articles_id` (`articles_id`);

--
-- Limitadores para a tabela `articles`
--
ALTER TABLE `articles`
ADD CONSTRAINT `fk_articles_authors` FOREIGN KEY (`authors_id`) REFERENCES `authors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_articles_categories1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `visitors`
--
ALTER TABLE `visitors`
ADD CONSTRAINT `fk_visitors_articles` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
