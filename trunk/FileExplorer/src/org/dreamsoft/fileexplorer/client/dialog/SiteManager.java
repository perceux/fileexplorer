package org.dreamsoft.fileexplorer.client.dialog;

import java.util.ArrayList;

import org.dreamsoft.fileexplorer.client.dialog.model.SiteModel;

public class SiteManager {
	// Site
	public static ArrayList<SiteModel> getSites() {
		ArrayList<SiteModel> sites = new ArrayList<SiteModel>();
		sites.add(new SiteModel("localhost", "http://localhost:8080/php/file.php", null, "http://localhost:8080/favicon.ico", "/dev"));
		sites.add(new SiteModel("fuckbox", "http://fuckbox.free.fr/file.php", null, "http://fuckbox.free.fr/favicon.ico", "/"));
		return sites;
	}
}
