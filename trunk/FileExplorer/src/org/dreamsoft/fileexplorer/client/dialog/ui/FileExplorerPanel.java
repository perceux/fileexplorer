package org.dreamsoft.fileexplorer.client.dialog.ui;

import org.dreamsoft.fileexplorer.client.dialog.controler.FileControler;
import org.dreamsoft.fileexplorer.client.dialog.controler.StoreController;
import org.dreamsoft.fileexplorer.client.dialog.model.Favorite;

import com.extjs.gxt.ui.client.Style.LayoutRegion;
import com.extjs.gxt.ui.client.Style.Scroll;
import com.extjs.gxt.ui.client.mvc.Dispatcher;
import com.extjs.gxt.ui.client.util.Margins;
import com.extjs.gxt.ui.client.widget.LayoutContainer;
import com.extjs.gxt.ui.client.widget.layout.BorderLayout;
import com.extjs.gxt.ui.client.widget.layout.BorderLayoutData;

public class FileExplorerPanel extends LayoutContainer {

	ActionPanel actionPanel = new ActionPanel();
	NavigationPanel navigationPanel = new NavigationPanel();
	FileDescription fileDescription = new FileDescription();
	FilePanel filePanel = new FilePanel(fileDescription);

	public FileExplorerPanel() {
		initUI();
		// Register the view/controler
		Dispatcher dispatcher = Dispatcher.get();
		dispatcher.addController(new FileControler(filePanel, fileDescription));
		dispatcher.addController(new StoreController("http://localhost:8080/php/file.php"));
	}

	protected void initUI() {

		final BorderLayout layout = new BorderLayout();
		setLayout(layout);

		filePanel.setScrollMode(Scroll.AUTOX);

		BorderLayoutData northData = new BorderLayoutData(LayoutRegion.NORTH, 50);
		northData.setCollapsible(true);
		northData.setFloatable(true);
		northData.setHideCollapseTool(true);
		northData.setSplit(true);
		northData.setMargins(new Margins(5, 5, 0, 5));

		BorderLayoutData westData = new BorderLayoutData(LayoutRegion.WEST, 150);
		westData.setSplit(true);
		westData.setCollapsible(true);
		westData.setMargins(new Margins(5));

		BorderLayoutData centerData = new BorderLayoutData(LayoutRegion.CENTER);
		centerData.setMargins(new Margins(5, 0, 5, 0));

		BorderLayoutData southData = new BorderLayoutData(LayoutRegion.SOUTH, 100);
		southData.setSplit(true);
		southData.setCollapsible(true);
		southData.setFloatable(true);
		southData.setMargins(new Margins(0, 5, 5, 5));

		add(actionPanel, northData);
		add(navigationPanel, westData);
		add(filePanel, centerData);
		add(fileDescription, southData);

	}

	public void addFavorite(Favorite favoriteP) {
		navigationPanel.addFavorite(favoriteP);
	}

}
