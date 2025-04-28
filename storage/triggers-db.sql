--
-- Triggers `comments`
--
DELIMITER $$
CREATE TRIGGER `after_comment_delete` AFTER DELETE ON `comments` FOR EACH ROW BEGIN
    UPDATE posts
    SET comment_count = comment_count - 1
    WHERE id = OLD.post_id; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_comment_insert` AFTER INSERT ON `comments` FOR EACH ROW BEGIN
    UPDATE posts
    SET comment_count = comment_count + 1
    WHERE id = NEW.post_id; END
$$
DELIMITER ;

--
-- Triggers `post_tags`
--
DELIMITER $$
CREATE TRIGGER `after_delete_post_tags` AFTER DELETE ON `post_tags` FOR EACH ROW BEGIN
    UPDATE tags
    SET count = count - 1
    WHERE id = OLD.tag_id; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_insert_post_tags` AFTER INSERT ON `post_tags` FOR EACH ROW BEGIN
    UPDATE tags
    SET count = count + 1
    WHERE id = NEW.tag_id; END
$$
DELIMITER ;
