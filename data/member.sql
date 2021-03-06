CREATE TABLE `members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `journey_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '旅行id',
  `uid`  VARCHAR(128)     NOT NULL DEFAULT '' COMMENT '成员id',
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '队长、队员',
  `intention` TINYINT NOT NULL DEFAULT 0 COMMENT '旅游意向',
  `free_time` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '空闲时间',
  `busy_time` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '忙碌时间',
  `memo`      VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT '新建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_uid`(`uid`, `created_at`),
  INDEX `idx_jid`(`journey_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;