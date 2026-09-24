#!/bin/bash
set -e

if [ ! -f dump_update.sql ]
then
    ssh dumb "mariadb-dump deltaruneboards \
        --single-transaction \
        --no-create-db \
        --compact \
        | gzip > /home/private/dump.sql.gz"
    scp dumb:/home/private/dump.sql.gz .
    ssh dumb "rm /home/private/dump.sql.gz"
    cat dump.sql.gz | gzip -d > dump_update.sql
    rm dump.sql.gz
    sed -i 's/ENGINE=InnoDB AUTO_INCREMENT=[0-9]* DEFAULT/ENGINE=InnoDB DEFAULT/g' dump_update.sql
fi

sudo mariadb -e "
DROP DATABASE IF EXISTS deltaruneboards_dump;
CREATE DATABASE deltaruneboards_dump;
USE deltaruneboards_dump;
SOURCE dump_update.sql;
DELETE FROM smf_admin_info_files;
DELETE FROM smf_approval_queue;
DELETE FROM smf_arcade_favorite;
UPDATE smf_arcade_games SET
    num_plays = 0,
    num_rates = 0,
    num_favorites = 0;
DELETE FROM smf_arcade_guest_data;
DELETE FROM smf_arcade_guest_extra_data;
DELETE FROM smf_arcade_matches;
DELETE FROM smf_arcade_matches_players;
DELETE FROM smf_arcade_matches_results;
DELETE FROM smf_arcade_matches_rounds;
DELETE FROM smf_arcade_member_data;
DELETE FROM smf_arcade_members;
DELETE FROM smf_arcade_newshouts;
DELETE FROM smf_arcade_pdl1;
DELETE FROM smf_arcade_pdl2;
DELETE FROM smf_arcade_rates;
DELETE FROM smf_arcade_scores;
DELETE FROM smf_attachments;
DELETE FROM smf_awards;
DELETE FROM smf_background_tasks;
DELETE FROM smf_ban_groups;
DELETE FROM smf_ban_items;
UPDATE smf_boards SET
    id_last_msg = 0,
    id_msg_updated = 0,
    num_topics = 0,
    num_posts = 0,
    unapproved_posts = 0,
    unapproved_topics = 0;
DELETE FROM smf_breeze_comments;
DELETE FROM smf_breeze_options;
DELETE FROM smf_breeze_status;
DELETE FROM smf_calendar;
DELETE FROM smf_calendar_holidays;
DELETE FROM smf_group_moderators;
DELETE FROM smf_interestmod;
DELETE FROM smf_log_actions;
DELETE FROM smf_log_activity;
DELETE FROM smf_log_banned;
DELETE FROM smf_log_boards;
DELETE FROM smf_log_comments;
DELETE FROM smf_log_digest;
DELETE FROM smf_log_errors;
DELETE FROM smf_log_floodcontrol;
DELETE FROM smf_log_group_requests;
DELETE FROM smf_log_mark_read;
DELETE FROM smf_log_notify;
DELETE FROM smf_log_online;
DELETE FROM smf_log_packages;
DELETE FROM smf_log_polls;
DELETE FROM smf_log_reported;
DELETE FROM smf_log_reported_comments;
DELETE FROM smf_log_scheduled_tasks;
DELETE FROM smf_log_search_messages;
DELETE FROM smf_log_search_results;
DELETE FROM smf_log_search_subjects;
DELETE FROM smf_log_spider_hits;
DELETE FROM smf_log_spider_stats;
DELETE FROM smf_log_subscribed;
DELETE FROM smf_log_topics;
DELETE FROM smf_mail_queue;
DELETE FROM smf_member_logins;
DELETE FROM smf_members;
DELETE FROM smf_mentions;
DELETE FROM smf_messages;
DELETE FROM smf_moderators;
DELETE FROM smf_personal_messages;
DELETE FROM smf_pm_labeled_messages;
DELETE FROM smf_pm_labels;
DELETE FROM smf_pm_recipients;
DELETE FROM smf_pm_rules;
DELETE FROM smf_poll_choices;
DELETE FROM smf_polls;
DELETE FROM smf_sessions;
DELETE FROM smf_stshop_inventory;
DELETE FROM smf_stshop_log_bank;
DELETE FROM smf_stshop_log_content;
DELETE FROM smf_stshop_log_games;
DELETE FROM smf_stshop_log_gift;
DELETE FROM smf_subscriptions;
DELETE FROM smf_themes WHERE id_member > 0;
DELETE FROM smf_topics;
DELETE FROM smf_user_alerts;
DELETE FROM smf_user_alerts_prefs WHERE id_member > 0;
DELETE FROM smf_user_drafts;
DELETE FROM smf_user_likes;
DELETE FROM smf_stshop_log_buy;
INSERT INTO smf_members VALUES
(
  -- id_member
  1,
  -- member_name
  'admin',
  -- date_registered
  0,
  -- posts
  0,
  -- id_group
  1,
  -- lngfile
  '',
  -- last_login
  0,
  -- real_name
  'admin',
  -- instant_messages
  0,
  -- unread_messages
  0,
  -- new_pm
  0,
  -- alerts
  0,
  -- buddy_list
  '',
  -- pm_ignore_list
  '',
  -- pm_prefs
  0,
  -- mod_prefs
  '',
  -- passwd
  '\$2y\$13\$BDtiyBKZihUJWMdAWBYpceViCbkzdws0VjC5asy6n7TpPFLvAp/PW',
  -- email_address
  'test@example.com',
  -- personal_text
  '',
  -- birthdate
  '1004-01-01',
  -- website_title
  '',
  -- website_url
  '',
  -- show_online
  1,
  -- time_format
  '',
  -- signature
  '',
  -- time_offset
  0,
  -- avatar
  '',
  -- usertitle
  '',
  -- member_ip
  'aaaa',
  -- member_ip2
  'aaaa',
  -- secret_question
  '',
  -- secret_answer
  '',
  -- id_theme
  0,
  -- is_activated
  1,
  -- validation_code
  '',
  -- id_msg_last_visit
  5,
  -- additional_groups
  '',
  -- smiley_set
  '',
  -- id_post_group
  4,
  -- total_time_logged_in
  0,
  -- password_salt
  '51d053c6f8e729c241ac337ecde8f55a',
  -- ignore_boards
  '',
  -- warning
  0,
  -- passwd_flood
  '',
  -- pm_receive_from
  1,
  -- timezone
  'UTC',
  -- tfa_secret
  '',
  -- tfa_backup
  '',
  -- shopMoney
  50,
  -- shopBank
  0,
  -- shopInventory_hide
  0,
  -- gamesPass
  0,
  -- mood_id
  0,
  -- mood_color
  '',
  -- pm_ignore_list_hide_posts
  1,
  -- pm_ignore_list_hide_topics
  1
);
UPDATE smf_arcade_modsettings SET value = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' WHERE variable = 'arcadeCookieEncryptionCipher';
UPDATE smf_arcade_modsettings SET value = 'aaaaaaaaaaaaaaaaaaaa' WHERE variable = 'arcadeRandomIdVar';
UPDATE smf_arcade_modsettings SET value = '1111111111' WHERE variable = 'arcadeSecretIdVar';
UPDATE smf_arcade_modsettings SET value = '/srv/dumb/Games' WHERE variable = 'gamesDirectory';
UPDATE smf_arcade_modsettings SET value = '/srv/dumb/ArcadeRetroArch/roms' WHERE variable = 'romGamesDirectory';
DELETE FROM smf_arcade_modsettings WHERE
    variable = 'arcadeDailyCronTasks' OR
    variable = 'arcadeRecurrentCronTasks' OR
    variable = 'game_of_day' OR
    variable = 'game_time';
UPDATE smf_settings SET value = '{\"1\":\"/srv/dumb/attachments\"}' WHERE variable = 'attachmentUploadDir';
UPDATE smf_settings SET value = '/srv/dumb/avatars' WHERE variable = 'avatar_directory';
UPDATE smf_settings SET value = '/srv/dumb/custom_avatar' WHERE variable = 'custom_avatar_dir';
UPDATE smf_settings SET value = '/srv/dumb/exports' WHERE variable = 'export_dir';
UPDATE smf_settings SET value = '/srv/dumb/Smileys' WHERE variable = 'smileys_dir';
UPDATE smf_settings SET value = '' WHERE variable = 'censor_proper' OR variable = 'censor_vulgar';
UPDATE smf_settings SET value = '0' WHERE
    variable = 'last_mod_report_action' OR
    variable = 'mail_type' OR
    variable = 'mail_recent' OR
    variable = 'maxMsgID' OR
    variable = 'memberlist_updated' OR
    variable = 'mostDate' OR
    variable = 'settings_updated' OR
    variable = 'turnstile_enabled';
UPDATE smf_settings SET value = '1' WHERE
    variable = 'latestMember' OR
    variable = 'mostOnline' OR
    variable = 'mostOnlineToday';
UPDATE smf_settings SET value = 'admin' WHERE variable = 'latestRealName';
UPDATE smf_settings SET value = '2000-01-01' WHERE variable = 'mostOnlineUpdated';
DELETE FROM smf_settings WHERE
    variable = 'next_task_time' OR
    variable = 'rand_seed' OR
    variable = 'search_pointer' OR
    variable = 'smtp_host' OR
    variable = 'smtp_password' OR
    variable = 'smtp_port' OR
    variable = 'smtp_username' OR
    variable = 'turnstile_private_key' OR
    variable = 'turnstile_public_key' OR
    variable = 'banLastUpdated' OR
    variable = 'calendar_updated' OR
    variable = 'browser_cache' OR
    variable = 'cron_last_checked';
UPDATE smf_themes SET value = '/srv/dumb/Themes/default' WHERE id_theme = 1 AND variable = 'theme_dir';
UPDATE smf_themes SET value = '/srv/dumb/Themes/DUMBDefault' WHERE id_theme = 2 AND variable = 'theme_dir';
UPDATE smf_themes SET value = '/srv/dumb/Themes/default' WHERE id_theme = 2 AND variable = 'based_on_dir';
"
sudo mariadb-dump deltaruneboards_dump --compact --no-autocommit=FALSE > dump.sql
sed -i 's/ENGINE=InnoDB AUTO_INCREMENT=[0-9]* DEFAULT/ENGINE=InnoDB DEFAULT/g' dump.sql
sudo mariadb -e "DROP DATABASE deltaruneboards_dump"
rm dump_update.sql
