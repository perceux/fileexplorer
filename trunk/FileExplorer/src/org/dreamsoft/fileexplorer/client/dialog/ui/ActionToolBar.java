package org.dreamsoft.fileexplorer.client.dialog.ui;

import org.dreamsoft.fileexplorer.client.dialog.controler.FilesEvents;
import org.dreamsoft.fileexplorer.client.dialog.ui.FilePanel.DisplayType;

import com.extjs.gxt.ui.client.event.ButtonEvent;
import com.extjs.gxt.ui.client.event.ComponentEvent;
import com.extjs.gxt.ui.client.event.Events;
import com.extjs.gxt.ui.client.event.Listener;
import com.extjs.gxt.ui.client.event.MenuEvent;
import com.extjs.gxt.ui.client.event.SelectionListener;
import com.extjs.gxt.ui.client.widget.button.Button;
import com.extjs.gxt.ui.client.widget.button.SplitButton;
import com.extjs.gxt.ui.client.widget.menu.CheckMenuItem;
import com.extjs.gxt.ui.client.widget.menu.Menu;
import com.extjs.gxt.ui.client.widget.menu.MenuItem;
import com.extjs.gxt.ui.client.widget.toolbar.ToolBar;

public class ActionToolBar extends ToolBar {

	// The split button which change the display type
	private SplitButton displaySplitItem;
	// The current display type
	private DisplayType currentType = DisplayType.MOSAIC;

	public ActionToolBar() {

		// Organize button
		Button organize = new Button(" Organize");
		add(organize);
		organize.setIconStyle("icon-organize");

		Menu organizeMenu = new Menu();
		CheckMenuItem menuItem = new CheckMenuItem("I Like Cats");
		menuItem.setChecked(true);
		organizeMenu.add(menuItem);

		menuItem = new CheckMenuItem("I Like Dogs");
		organizeMenu.add(menuItem);
		organize.setMenu(organizeMenu);

		// The display type button
		displaySplitItem = new SplitButton(" Display");
		displaySplitItem.setIconStyle("icon-display-mosaic");
		displaySplitItem.addSelectionListener(new SelectionListener<ButtonEvent>() {
			@Override
			public void componentSelected(ButtonEvent ceP) {
				int ord = currentType.ordinal() + 1;
				if (ord >= DisplayType.values().length) {
					ord = 0;
				}
				DisplayType newType = DisplayType.values()[ord];
				changeDisplay(newType);
			}
		});
		displaySplitItem.addListener(Events.ArrowClick, new Listener<ComponentEvent>() {
			public void handleEvent(ComponentEvent ce) {
				ce.getClientX();
			}
		});

		Menu displayMenu = new Menu();
		MenuItem item;
		item = new MenuItem("Mosaic");
		item.setIconStyle("icon-display-mosaic");
		item.addSelectionListener(new SelectionListener<MenuEvent>() {
			public void componentSelected(MenuEvent ceP) {
				changeDisplay(DisplayType.MOSAIC);
			}
		});
		displayMenu.add(item);
		item = new MenuItem("List");
		item.setIconStyle("icon-display-list");
		item.addSelectionListener(new SelectionListener<MenuEvent>() {
			public void componentSelected(MenuEvent ceP) {
				changeDisplay(DisplayType.LIST);
			}
		});
		displayMenu.add(item);
		item = new MenuItem("Detail");
		item.setIconStyle("icon-display-detail");
		item.addSelectionListener(new SelectionListener<MenuEvent>() {
			public void componentSelected(MenuEvent ceP) {
				changeDisplay(DisplayType.DETAIL);
			}
		});
		displayMenu.add(item);
		displaySplitItem.setMenu(displayMenu);

		add(displaySplitItem);

		// New directory button
		Button newDirectory = new Button("New directory");
		newDirectory.setIconStyle("icon-newdirectory");
		add(newDirectory);
	}

	/**
	 * Change the current display
	 * 
	 * @param typeP
	 */
	private void changeDisplay(DisplayType typeP) {
		if (typeP == DisplayType.MOSAIC) {
			displaySplitItem.setIconStyle("icon-display-mosaic");
		} else if (typeP == DisplayType.LIST) {
			displaySplitItem.setIconStyle("icon-display-list");
		} else if (typeP == DisplayType.DETAIL) {
			displaySplitItem.setIconStyle("icon-display-detail");
		}
		currentType = typeP;
		// Fire the event
		FilesEvents.fireDisplayTypeChanged(typeP);
	}

}
