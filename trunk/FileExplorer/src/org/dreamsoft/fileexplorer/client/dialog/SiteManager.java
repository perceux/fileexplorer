package org.dreamsoft.fileexplorer.client.dialog;

import java.util.ArrayList;

import org.dreamsoft.fileexplorer.client.dialog.model.SiteModel;

import com.google.gwt.user.client.Window;

public class SiteManager {
	
	public static String getLocalUrl() {
		String hostname = Window.Location.getHostName();
		String port = Window.Location.getPort();
		String serverUrl;
		if (port.equals("9999") || port.equals("")) {
			serverUrl = "http://" + hostname;
		} else {
			serverUrl = "http://" + hostname + ":" + port;
		}
		return serverUrl;
	}
	
	// Site
	public static ArrayList<SiteModel> getSites() {
		ArrayList<SiteModel> sites = new ArrayList<SiteModel>();
		sites.add(new SiteModel("localhost",  getLocalUrl() + "/php/file.php", null, getLocalUrl() +"/favicon.ico", "/dev"));
		sites.add(new SiteModel("fuckbox", "http://fuckbox.free.fr/file.php", null, "http://fuckbox.free.fr/favicon.ico", "/"));
		return sites;
	}
}
