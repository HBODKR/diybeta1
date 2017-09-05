
CREATE TABLE /*_*/userspageslinks (
  -- Key to user.user_id
  upl_user_id int unsigned NOT NULL,

  -- Key to the page
  upl_page_namespace int(10) unsigned DEFAULT NULL,
  upl_page_title varbinary(255) DEFAULT NULL,
  upl_page_id int(10) unsigned DEFAULT NULL,
  
  upl_type varbinary(255) DEFAULT '',

  -- Timestamp used to send notification e-mails and show "updated since last visit" markers on
  -- history and recent changes / watchlist. Set to NULL when the user visits the latest revision
  -- of the page, which means that they should be sent an e-mail on the next change.
  upl_notificationtimestamp varbinary(14)

) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/upl_user_id ON /*_*/userspageslinks (upl_user_id, upl_type);
CREATE INDEX /*i*/upl_page ON /*_*/userspageslinks (upl_page_namespace, upl_page_title, upl_type);
CREATE INDEX /*i*/upl_page_id ON /*_*/userspageslinks (upl_page_id, upl_type);


