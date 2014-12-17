
INSERT INTO mailbox (username, password, name, storagebasedirectory, maildir, quota, domain, active)
    VALUES ('aafetsrom@bullion.com.gh', '$1$ru3duhyo$x/s3yu5VD5qpdgT94wZV.0', 'aafetsrom', '/var/vmail/vmail1', 'bullion.com.gh/a/aa/aaf/aafetsrom-2014.12.16.19.48.02/', '100', 'bullion.com.gh', '1');
INSERT INTO alias (address, goto, created, active) VALUES ('aafetsrom@bullion.com.gh', 'aafetsrom@bullion.com.gh', NOW(), 1);
