
CREATE TABLE /*_*/pagesbelonging (
  -- Key to page.page_id
  pb_parent_page_id int(11) UNSIGNED NOT NULL,

  pb_child_page_id int(11) UNSIGNED NOT NULL,

  -- Timestamp used to send notification e-mails and show "updated since last visit" markers on
  -- history and recent changes / watchlist. Set to NULL when the user visits the latest revision
  -- of the page, which means that they should be sent an e-mail on the next change.
  pb_notificationtimestamp varbinary(14)

) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/pb_parent_page_id ON /*_*/pagesbelonging (pb_parent_page_id);
CREATE UNIQUE INDEX /*i*/pb_belonging ON /*_*/pagesbelonging (pb_parent_page_id, pb_child_page_id);
CREATE INDEX /*i*/pb_notificationtimestamp ON /*_*/pagesbelonging (pb_parent_page_id, pb_notificationtimestamp);


