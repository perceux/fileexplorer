package org.dreamsoft.fileexplorer.client;

import org.dreamsoft.fileexplorer.client.dialog.model.Favorite;
import org.dreamsoft.fileexplorer.client.dialog.ui.FileExplorerPanel;

import com.extjs.gxt.ui.client.GXT;
import com.extjs.gxt.ui.client.widget.Viewport;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;
import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.ui.RootPanel;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class FileExplorer implements EntryPoint {

	private FileExplorerPanel fileExplorerPanel = null;

	public void onModuleLoad() {
		GXT.hideLoadingPanel("loading"); // hide loading ...
		Viewport viewport = new Viewport();
		viewport.setLayout(new FitLayout());
		createDialog();
		viewport.add(fileExplorerPanel);
		RootPanel.get().add(viewport);
	}

	private void createDialog() {
		fileExplorerPanel = new FileExplorerPanel();
		fileExplorerPanel.addFavorite(new Favorite("Computer", "/windows", "icon-computer"));
		fileExplorerPanel.addFavorite(new Favorite("Network", "/", "icon-network"));
		fileExplorerPanel.addFavorite(new Favorite("Projects", "/dev", "icon-favorite"));
	}
}
