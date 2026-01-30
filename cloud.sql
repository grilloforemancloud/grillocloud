-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 09/12/2025 às 23:01
-- Versão do servidor: 11.8.2-MariaDB-1 from Debian
-- Versão do PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cloud`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Container`
--

CREATE TABLE `Container` (
  `id` int(8) NOT NULL,
  `nome` varchar(18) NOT NULL,
  `portA` int(5) NOT NULL,
  `portB` int(5) NOT NULL,
  `timeInput` date NOT NULL,
  `timeEnd` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `Container`
--

INSERT INTO `Container` (`id`, `nome`, `portA`, `portB`, `timeInput`, `timeEnd`) VALUES
(1, 'ct0', 502, 503, '2025-10-15', '2025-10-15'),
(1, 'ct0b', 505, 506, '2025-10-15', '2025-10-15'),
(1, 'boi', 507, 508, '2025-10-15', '2025-10-15'),
(1, 'ct0', 502, 503, '2025-10-15', '2025-10-15'),
(1, 'bravo', 510, 511, '2025-10-15', '2025-10-15'),
(1, 'ct0rrrr', 444, 0, '2025-10-17', '2025-10-18'),
(1, 'ct01', 333, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0777', 333, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'boca', 111, 0, '2025-10-17', '2025-10-18'),
(1, 'buiuii', 111, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0aaa', 666, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0aa', 444, 0, '2025-10-17', '2025-10-18'),
(1, 'bui', 666, 0, '2025-10-17', '2025-10-18'),
(1, 'gerou', 777, 0, '2025-10-17', '2025-10-18'),
(1, 'falar', 111, 0, '2025-10-17', '2025-10-18'),
(1, 'vasco', 502, 0, '2025-10-17', '2025-10-18'),
(1, 'bora', 666, 0, '2025-10-17', '2025-10-18'),
(1, '111', 234, 0, '2025-10-17', '2025-10-18'),
(1, 'aba', 555, 0, '2025-10-17', '2025-10-18'),
(1, 'ct0', 502, 0, '2025-10-17', '2025-10-18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `User`
--

CREATE TABLE `User` (
  `id` int(8) NOT NULL,
  `nome` varchar(18) NOT NULL,
  `passworda` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `User`
--

INSERT INTO `User` (`id`, `nome`, `passworda`) VALUES
(1, 'teste', 'teste3');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `Container`
--
ALTER TABLE `Container`
  ADD KEY `pk_id` (`id`);

--
-- Índices de tabela `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `Container`
--
ALTER TABLE `Container`
  ADD CONSTRAINT `pk_id` FOREIGN KEY (`id`) REFERENCES `User` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
