/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui.detail;

import java.util.ArrayList;
import java.util.List;

import org.dreamsoft.fileexplorer.client.dialog.controler.FilesEvents;
import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.event.Events;
import com.extjs.gxt.ui.client.event.GridEvent;
import com.extjs.gxt.ui.client.event.Listener;
import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.grid.ColumnConfig;
import com.extjs.gxt.ui.client.widget.grid.ColumnData;
import com.extjs.gxt.ui.client.widget.grid.ColumnModel;
import com.extjs.gxt.ui.client.widget.grid.Grid;
import com.extjs.gxt.ui.client.widget.grid.GridCellRenderer;
import com.extjs.gxt.ui.client.widget.grid.GridGroupRenderer;
import com.extjs.gxt.ui.client.widget.grid.GroupColumnData;
import com.extjs.gxt.ui.client.widget.grid.GroupingView;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;
import com.google.gwt.i18n.client.DateTimeFormat;

/**
 * This panel shows file details (it uses a grid to display files information)
 * 
 * @author Eric Taix
 */
public class FileDetail extends AbstractFileUI {

	private Grid<FileModel> grid;
	private ColumnModel cm;

	/**
	 * Default constructor
	 */
	public FileDetail() {
		initUI();
	}

	/**
	 * Initialize the user interface
	 */
	protected void initUI() {
		setId("file-detail-id");
		setLayout(new FitLayout());
		setBorders(true);

		// Define columns config
		List<ColumnConfig> configs = new ArrayList<ColumnConfig>();
		ColumnConfig column = new ColumnConfig("icon16x16", "", 22);
		column.setRenderer(new GridCellRenderer<FileModel>() {
			public String render(FileModel model, String property, ColumnData config, int rowIndex, int colIndex, ListStore<FileModel> store) {
				return "<img src='" + model.getIcon16x16() + "'/>";
			}

		});
		configs.add(column);
		column = new ColumnConfig("name", "Name", 200);
		configs.add(column);
		column = new ColumnConfig("mtime", "Last modified", 100);
		column.setDateTimeFormat(DateTimeFormat.getShortDateFormat());
		configs.add(column);
		column = new ColumnConfig("ctime", "Created", 100);
		column.setDateTimeFormat(DateTimeFormat.getShortDateFormat());
		configs.add(column);
		column = new ColumnConfig("size", "Size", 75);
		configs.add(column);
		column = new ColumnConfig("ext", "Type", 100);
		configs.add(column);

		cm = new ColumnModel(configs);
		grid = new Grid<FileModel>(null, cm);
		
		grid.setAutoExpandColumn("name");
		grid.setBorders(false);

		// Handle the DoubleClick event
		grid.addListener(Events.RowDoubleClick, new Listener<GridEvent>() {
			public void handleEvent(GridEvent be) {
				FileModel fileModel = grid.getSelectionModel().getSelectedItem();
				if (fileModel != null) {
					// If it's a directory then change the current
					if (fileModel.isDirectory()) {
						FilesEvents.fireDirectoryChanged(getFullPath(fileModel.getName()));
					}
					// It's a file, so fire onSuccess event
					else {

					}
				}
			}
		});

		// Handle the click event (file selection)
		grid.addListener(Events.RowClick, new Listener<GridEvent>() {
			public void handleEvent(GridEvent be) {
				FileModel fileModel = grid.getSelectionModel().getSelectedItem();
				FilesEvents.fireCurrentFileChanged(fileModel);
			}
		});

		add(grid);
	}


	public void setStore(ListStore<FileModel> storeP) {
		grid.reconfigure(storeP, cm);
	}

}
