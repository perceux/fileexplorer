/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import org.dreamsoft.fileexplorer.client.dialog.SiteManager;
import org.dreamsoft.fileexplorer.client.dialog.controler.AddressControler;
import org.dreamsoft.fileexplorer.client.dialog.controler.FilesEvents;
import org.dreamsoft.fileexplorer.client.dialog.model.SiteModel;

import com.extjs.gxt.ui.client.event.ButtonEvent;
import com.extjs.gxt.ui.client.event.ComponentEvent;
import com.extjs.gxt.ui.client.event.Events;
import com.extjs.gxt.ui.client.event.KeyListener;
import com.extjs.gxt.ui.client.event.Listener;
import com.extjs.gxt.ui.client.event.SelectionChangedEvent;
import com.extjs.gxt.ui.client.event.SelectionChangedListener;
import com.extjs.gxt.ui.client.mvc.Dispatcher;
import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.button.Button;
import com.extjs.gxt.ui.client.widget.form.ComboBox;
import com.extjs.gxt.ui.client.widget.form.TextField;
import com.extjs.gxt.ui.client.widget.form.ComboBox.TriggerAction;
import com.extjs.gxt.ui.client.widget.toolbar.SeparatorToolItem;
import com.extjs.gxt.ui.client.widget.toolbar.ToolBar;

/**
 * The panel which contains the adress bar, history's buttons
 * 
 * @author Eric Taix
 */
public class AddressToolBar extends ToolBar {

	// The history list
	private List<String> history = new ArrayList<String>();
	// The current index
	private int currentHistory;

	// Adress field
	private TextField<String> address;
	// Previous button
	private Button prev;
	// Next button
	private Button next;
	// Next button
	private Button refresh;
	// Flag to prevent from adding to history
	private boolean fromNavigation = false;

	/**
	 * Constructor
	 */
	public AddressToolBar() {
		initUI();
		// 
		Dispatcher dispatcher = Dispatcher.get();
		dispatcher.addController(new AddressControler(this));
	}

	/**
	 * Initialize the user interface
	 */
	protected void initUI() {
		addStyleName("address-tb");
		prev = new Button("", "icon-previous");
		prev.setEnabled(false);
		prev.addListener(Events.Select, new Listener<ButtonEvent>() {
			public void handleEvent(ButtonEvent be) {
				String dir = history.get(currentHistory - 1);
				currentHistory--;
				updateHistory();
				fromNavigation = true;
				FilesEvents.fireDirectoryChanged(dir);
			}
		});
		add(prev);
		next = new Button("", "icon-next");
		next.setEnabled(false);
		next.addListener(Events.Select, new Listener<ButtonEvent>() {
			public void handleEvent(ButtonEvent be) {
				String dir = history.get(currentHistory + 1);
				currentHistory++;
				updateHistory();
				fromNavigation = true;
				FilesEvents.fireDirectoryChanged(dir);
			}
		});
		add(next);
		// Address
		add(new SeparatorToolItem());
		address = new TextField<String>();
		address.addKeyListener(new KeyListener() {
			@Override
			public void componentKeyPress(ComponentEvent event) {
				super.componentKeyPress(event);
				if (event.getKeyCode() == 13) {
					fromNavigation = true;
					FilesEvents.fireDirectoryChanged(address.getValue());
				}
			}
		});
		address.setWidth("300");
		address.setValue("/");
		add(address);
		// Refresh
		refresh = new Button("", "x-tbar-refresh");
		refresh.addListener(Events.Select, new Listener<ButtonEvent>() {
			public void handleEvent(ButtonEvent be) {
				fromNavigation = true;
				FilesEvents.fireDirectoryChanged(address.getValue());
			}
		});
		add(refresh);

		ComboBox<SiteModel> combo2 = new ComboBox<SiteModel>();
		combo2.setWidth(150);
		ListStore<SiteModel> store = new ListStore<SiteModel>();
		for (Iterator<SiteModel> iterator = SiteManager.getSites().iterator(); iterator.hasNext();) {
			store.add(iterator.next());
		}
		combo2.setStore(store);
		combo2.setTemplate(getFlagTemplate());
		combo2.setDisplayField("name");
		combo2.setTypeAhead(true);
		combo2.setTriggerAction(TriggerAction.ALL);
		combo2.addSelectionChangedListener(new SelectionChangedListener<SiteModel>() {
			@Override
			public void selectionChanged(SelectionChangedEvent<SiteModel> se) {
				FilesEvents.fireSiteChanged((String) se.getSelectedItem().getUrl());
			}
		});
		add(combo2);

		// initialize the history
		initHistory();
	}

	private native String getFlagTemplate() /*-{
		return  [ 
		'<tpl for=".">', 
		'<div class="x-combo-list-item"><img width="16px" height="11px" src="{[values.imageUrl]}"> {[values.name]}</div>', 
		'</tpl>' 
		].join("");
	}-*/;

	/**
	 * Initialize (clear) the history list
	 */
	public void initHistory() {
		history = new ArrayList<String>();
		next.setEnabled(false);
		prev.setEnabled(false);
	}

	/**
	 * Add a new history
	 * 
	 * @param historyP
	 */
	private void addHistory(String dir) {
		if (!fromNavigation) {
			history.add(dir);
			currentHistory = history.size() - 1;
			updateHistory();
		}
		fromNavigation = false;
	}

	/**
	 * Update navigation buttons state and tips
	 */
	private void updateHistory() {
		if (currentHistory < (history.size() - 1)) {
			next.setEnabled(true);
			next.setTitle(history.get(currentHistory + 1));
		} else {
			next.setEnabled(false);
			next.setTitle("");
		}
		if (currentHistory > 0) {
			prev.setEnabled(true);
			prev.setTitle(history.get(currentHistory - 1));
		} else {
			prev.setEnabled(false);
			prev.setTitle("");
		}
	}

	/**
	 * Change the current history
	 */
	public void changeDirectory(String dir) {
		if (dir == null) {
			address.setValue("/");
		} else {
			address.setValue(dir);
		}
		addHistory(dir);
	}

}
