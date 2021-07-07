/* CREATE TABLES */
CREATE TABLE IF NOT EXISTS "NodeModules" (
	"id"	INTEGER NOT NULL UNIQUE,
	"Title"	TEXT NOT NULL,
	"Homepage_URL"	TEXT,
	"Repo_URL"	TEXT,
	"Repo_Type"	TEXT,
	"Description"	TEXT,
	"License"	TEXT,
	"Is_WP_Dependency"	INTEGER NOT NULL DEFAULT 0,
	"Author_Name"	TEXT,
	"Author_EmailAddress"	TEXT,
	"Author_URL"	TEXT,
	"_deleted"	INTEGER NOT NULL DEFAULT 0,
	"_scanned" INTEGER DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "NodeModules_NodeModules" (
	"nodemodule_parent_id"	INTEGER NOT NULL,
	"nodemodule_child_id"	INTEGER NOT NULL,
	PRIMARY KEY("nodemodule_parent_id","nodemodule_child_id")
);

CREATE TABLE IF NOT EXISTS "Plugins_NodeModules" (
	"plugin_id"	INTEGER NOT NULL,
	"nodemodule_id"	INTEGER NOT NULL,
	PRIMARY KEY("plugin_id","nodemodule_id")
);

CREATE TABLE IF NOT EXISTS "Themes_NodeModules" (
	"theme_id"	INTEGER NOT NULL,
	"nodemodule_id"	INTEGER NOT NULL,
	PRIMARY KEY("theme_id","nodemodule_id")
);

CREATE TABLE IF NOT EXISTS "Options" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"value"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "Plugins" (
	"id"	INTEGER NOT NULL UNIQUE,
	"Title"	TEXT NOT NULL,
	"Folder_Name"	TEXT,
	"Description"	TEXT,
	"url" TEXT,
	_deleted INTEGER NOT NULL DEFAULT 0, 
	is_internal INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "Sites" (
	"id"	INTEGER NOT NULL UNIQUE,
	"Title"	TEXT NOT NULL,
	"URL"	INTEGER,
	"Admin_URL" TEXT,
	"Host"	TEXT,
	"PHP_Version_Major"	INTEGER,
	"PHP_Version_Minor"	INTEGER,
	"PHP_Version_Patch"	INTEGER,
	"WordPress_Version_Major"	INTEGER,
	"WordPress_Version_Minor"	INTEGER,
	"WordPress_Version_Patch"	INTEGER,
	"Has_2Factor"	INTEGER NOT NULL DEFAULT 0,
	"Description"	TEXT,
	"SSL_Expiry"	INTEGER,
	"SSL_Provider"	TEXT,
	"Email_From_Name"	TEXT,
	"Email_From_Address"	TEXT,
	"Admin_Address"	TEXT, 
	Last_Reviewed TEXT, 
	_deleted INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "Sites_Plugins" (
	"Site_id"	INTEGER NOT NULL,
	"Plugin_id"	INTEGER NOT NULL,
	"Is_Active"	INTEGER DEFAULT 0,
	"Can_Update"	INTEGER DEFAULT 0,
	PRIMARY KEY("Site_id","Plugin_id")
);

CREATE TABLE IF NOT EXISTS "Sites_Themes" (
	"Site_id"	INTEGER NOT NULL,
	"Theme_id"	INTEGER NOT NULL,
	"Is_Primary"	INTEGER DEFAULT 0,
	"Can_Update"	INTEGER DEFAULT 0,
	PRIMARY KEY("Site_id","Theme_id")
);

CREATE TABLE IF NOT EXISTS "Sites_Users" (
	"Site_id"	INTEGER NOT NULL,
	"User_id"	INTEGER NOT NULL,
	"Username"	TEXT NOT NULL,
	"Role"	TEXT NOT NULL,
	PRIMARY KEY("Site_id","User_id")
);

CREATE TABLE IF NOT EXISTS "Themes" (
	"id"	INTEGER NOT NULL UNIQUE,
	"Title"	TEXT NOT NULL,
	"Folder_Name"	TEXT,
	"Description"	TEXT,
	"Parent_ID" INTEGER,
	_deleted INTEGER NOT NULL DEFAULT 0, 
	is_internal INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "Users" (
	"id"	INTEGER NOT NULL UNIQUE,
	"EmailAddress"	TEXT,
	"Name_First"	TEXT,
	"Name_Last"	TEXT,
	"Is_Active"	INTEGER NOT NULL DEFAULT 1, 
	_deleted INTEGER NOT NULL DEFAULT 0, 
	is_employee INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY("id" AUTOINCREMENT)
);

/* DEFAULT DATA */
INSERT INTO Options (
	name,
	value
)
VALUES (
	'review_ttl_days',
	40
);

/* CREATE VIEWS */
DROP VIEW IF EXISTS vPlugins;
CREATE VIEW vPlugins AS 
SELECT
	Plugins.id,
	Plugins.Title,
	Plugins.Description,
	Plugins.Folder_Name,
	Plugins.url,
	Plugins.is_internal,
	(
		Plugins.Title 
		|| ' ' || Plugins.Folder_Name 
		|| ' ' || Plugins.Description
	) AS search_text,
	(
		SELECT 
			COUNT(*) 
		FROM 
			Sites_Plugins sp
			INNER JOIN
				Sites s
			ON
				sp.Site_id = s.id
				AND s._deleted = 0
		WHERE 
			sp.plugin_id = Plugins.id
	) as sites_count
FROM
	Plugins
WHERE
	Plugins._deleted = 0
;
	
DROP VIEW IF EXISTS vSites;
CREATE VIEW vSites as 
SELECT
	Sites.id,
	Sites.Title,
	Sites.URL,
	Sites.Admin_URL,
	Sites.Host,
	Sites.Description,
	Sites.PHP_Version_Major,
	Sites.PHP_Version_Minor,
	Sites.PHP_Version_Patch,
	Sites.WordPress_Version_Major,
	Sites.WordPress_Version_Minor,
	Sites.WordPress_Version_Patch,
	Sites.SSL_Expiry,
	Sites.SSL_Provider,
	Sites.Email_From_Name,
	Sites.Email_From_Address,
	Sites.Admin_Address,
	Sites.Has_2Factor,
	Sites.Last_Reviewed,
	(
		SELECT COUNT(*) 
		FROM 
			Sites_Plugins 
			INNER JOIN
				Plugins
			ON
				Sites_Plugins.Plugin_id = Plugins.id
				AND Plugins._deleted = 0
		WHERE 
			Sites_Plugins.Site_id = Sites.id
	) AS Plugins_Installed,
	(
		SELECT COUNT(*) 
		FROM 
			Sites_Plugins 
			INNER JOIN
				Plugins
			ON
				Sites_Plugins.Plugin_id = Plugins.id
				AND Plugins._deleted = 0
		WHERE 
			Sites_Plugins.Site_id = Sites.id
			AND Sites_Plugins.Is_Active = 1
	) AS Plugins_Active,
	(
		SELECT COUNT(*) 
		FROM 
			Sites_Themes 
			INNER JOIN
				Themes
			ON
				Sites_Themes.Theme_id = Themes.id
				AND Themes._deleted = 0
		WHERE 
			Sites_Themes.Site_id = Sites.id
	) AS Themes_Installed,
	(
		SELECT 
			Themes.Title 
		FROM 
			Themes 
			INNER JOIN 
				Sites_Themes 
			ON 
				Sites_Themes.Theme_id = Themes.id 
				AND Sites_Themes.Site_id = Sites.id 
				AND Sites_Themes.Is_Primary = 1
		WHERE
			Themes._deleted = 0
	) AS Primary_Theme,
	(
		SELECT COUNT(*) 
		FROM 
			Sites_Users 
			INNER JOIN
				Users
			ON
				Sites_Users.User_id = Users.id
				AND Users._deleted = 0
		WHERE 
			Sites_Users.Site_id = Sites.id
	) AS Users_Count,
	Sites.PHP_Version_Major || '.' || Sites.PHP_Version_Minor || '.' || Sites.PHP_Version_Patch as PHP_Version,
	Sites.WordPress_Version_Major || '.' || Sites.WordPress_Version_Minor || '.' || Sites.WordPress_Version_Patch as WordPress_Version,
	(
		CASE 
			when ROUND(julianday('now') - julianday(Sites.Last_Reviewed)) > (SELECT value FROM Options WHERE name='review_ttl_days') THEN 1
			ELSE 0
		END
	) AS LastReview_IsExpired,
	ROUND(julianday('now') - julianday(Sites.Last_Reviewed)) AS LastReview_DaysSince,
	(
		Sites.title 
		|| ' ' || Sites.host 
		|| ' ' || Sites.url 
		|| ' ' || Sites.description
	) AS search_text
FROM
	Sites
WHERE
	_deleted = 0
;
	
DROP VIEW IF EXISTS vSites_Plugins;
CREATE VIEW vSites_Plugins AS
SELECT
	Sites_Plugins.Site_id,
	Sites_Plugins.Plugin_id,
	Sites.Title AS Site_Title,
	Plugins.Title AS Plugin_Title,
	Sites_Plugins.Is_Active,
	Sites_Plugins.Can_Update
FROM
	Sites_Plugins
	INNER JOIN
		Sites
	ON
		Sites_Plugins.Site_id = Sites.id
		AND Sites._deleted = 0
	INNER JOIN 
		Plugins
	ON
		Sites_Plugins.Plugin_id = Plugins.id
		AND Plugins._deleted = 0
;

DROP VIEW IF EXISTS vSites_Themes;
CREATE VIEW vSites_Themes AS
SELECT
	Sites_Themes.Site_id,
	Sites_Themes.Theme_id,
	Sites.Title AS Site_Title,
	Themes.Title AS Theme_Title,
	Sites_Themes.Is_Primary,
	Sites_Themes.Can_Update,
	PThemes.Title AS Parent_Title
FROM
	Sites_Themes
	INNER JOIN
		Sites
	ON
		Sites_Themes.Site_id = Sites.id
		AND Sites._deleted = 0
	INNER JOIN 
		Themes
	ON
		Sites_Themes.Theme_id = Themes.id
		AND Themes._deleted = 0
	LEFT OUTER JOIN
		Themes PThemes
	ON
		Themes.Parent_id = PThemes.id
		and PThemes._deleted = 0
;

DROP VIEW IF EXISTS vSites_Users;
CREATE VIEW vSites_Users AS
SELECT
	Sites_Users.Site_id,
	Sites_Users.User_id,
	Sites.Title AS Site_Title,
	Users.EmailAddress,
	Users.Name_First,
	Users.Name_Last,
	Sites_Users.Username,
	Sites_Users.Role,
	Users.Is_Active,
	Users.is_employee
FROM
	Sites_Users
	INNER JOIN
		Sites
	ON
		Sites_Users.Site_id = Sites.id
		AND Sites._deleted = 0
	INNER JOIN 
		Users
	ON
		Sites_Users.User_id = Users.id
		AND Users._deleted = 0
;

DROP VIEW IF EXISTS vThemes;
CREATE VIEW vThemes AS 
SELECT
	Themes.*
FROM
	Themes
WHERE
	Themes._deleted = 0
;

DROP VIEW IF EXISTS vUsers;
CREATE VIEW vUsers AS 
SELECT
	Users.*
FROM
	Users
WHERE
	Users._deleted = 0
;

DROP VIEW IF EXISTS vNodeModules;
CREATE VIEW vNodeModules AS 
SELECT
	NodeModules.*,
	(
		SELECT 
			COUNT(*) 
		FROM 
			NodeModules_NodeModules
			INNER JOIN
				NodeModules nParents
			ON
				NodeModules_NodeModules.nodemodule_parent_id = nParents.id
				AND nParents._deleted = 0
		WHERE
			NodeModules_NodeModules.nodemodule_child_id = NodeModules.id
	) AS Parents_Count,
	(
		SELECT 
			COUNT(*) 
		FROM 
			NodeModules_NodeModules
			INNER JOIN
				NodeModules nChildren
			ON
				NodeModules_NodeModules.nodemodule_child_id = nChildren.id
				AND nChildren._deleted = 0
		WHERE
			NodeModules_NodeModules.nodemodule_parent_id = NodeModules.id
	) AS Children_Count,
	(
		NodeModules.title || ' ' 
		|| NodeModules.description || ' '
		|| NodeModules.author_name
	) AS search_text
FROM 
	NodeModules
WHERE
	_deleted = 0
;

DROP VIEW IF EXISTS vNodeModules_NodeModules;
CREATE VIEW vNodeModules_NodeModules AS 
SELECT
	nm_nm.*,
	nmParents.title AS parent_title,
	nmChildren.title AS child_title
FROM
	NodeModules_NodeModules nm_nm
	INNER JOIN
		NodeModules nmParents
	ON
		nm_nm.nodemodule_parent_id = nmParents.id
		AND nmParents._deleted = 0
	INNER JOIN
		NodeModules nmChildren
	ON
		nm_nm.nodemodule_child_id = nmChildren.id
		AND nmChildren._deleted = 0		
;

DROP VIEW IF EXISTS vPlugins_NodeModules;
CREATE VIEW vPlugins_NodeModules AS 
SELECT
	pnm.*,
	p.Title as plugin_title,
	nm.Title as nodemodule_title
FROM
	Plugins_NodeModules pnm
	INNER JOIN
		Plugins p
	ON
		pnm.plugin_id = p.id
		and p._deleted - 0
	INNER JOIN
		NodeModules nm
	ON
		pnm.nodemodule_id = nm.id
		AND nm._deleted = 0
;

DROP VIEW IF EXISTS vThemes_NodeModules;
CREATE VIEW vThemes_NodeModules AS 
SELECT
	tnm.*,
	t.Title as theme_title,
	nm.Title as nodemodule_title
FROM
	Themes_NodeModules tnm
	INNER JOIN
		Themes t
	ON
		tnm.theme_id = t.id
		and t._deleted - 0
	INNER JOIN
		NodeModules nm
	ON
		tnm.nodemodule_id = nm.id
		AND nm._deleted = 0
;
