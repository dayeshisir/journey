CREATE TABLE `users`(
  `id` BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `openid` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '用户唯一标识',
  `unionid` VARCHAR (128) NOT NULL DEFAULT ''COMMENT '用户在开放平台的唯一标识符',
  `gender`  TINYINT NOT NULL DEFAULT 0 COMMENT '性别 1:男性 2:女性 0:未知',
  `portrait` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '旅行id',
  `nick_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT '新建时间',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_id`(`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;