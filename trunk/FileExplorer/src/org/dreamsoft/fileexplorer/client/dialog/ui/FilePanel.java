/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui;

import java.util.ArrayList;

import org.dreamsoft.fileexplorer.client.dialog.ui.detail.AbstractFileUI;
import org.dreamsoft.fileexplorer.client.dialog.ui.detail.FileDetail;
import org.dreamsoft.fileexplorer.client.dialog.ui.detail.FileList;
import org.dreamsoft.fileexplorer.client.dialog.ui.detail.FileMosaic;

import com.extjs.gxt.ui.client.widget.Component;
import com.extjs.gxt.ui.client.widget.LayoutContainer;
import com.extjs.gxt.ui.client.widget.layout.CardLayout;

/**
 * The panel which contains files details
 * 
 * @author Eric Taix
 */
public class FilePanel extends LayoutContainer {
	public enum DisplayType {
		MOSAIC, LIST, DETAIL;
	}

	private CardLayout layout;

	/**
	 * Constructor
	 */
	public FilePanel(FileDescription fileDescriptionP) {
		initUI();
	}

	/**
	 * Initialiaze the user interface
	 */
	protected void initUI() {
		setBorders(false);
		layout = new CardLayout();
		setLayout(layout);

		// Create mosaic panel
		FileMosaic fileMosaic = new FileMosaic();
		add(fileMosaic);

		// Create detail panel
		FileList fileList = new FileList();
		add(fileList);

		// Create detail panel
		FileDetail fileDetail = new FileDetail();
		add(fileDetail);

		layout.setActiveItem(getItem(0));
	}

	/**
	 * Change the display type
	 * 
	 * @param typeP
	 */
	public void changeDisplay(DisplayType typeP) {
		if (typeP == DisplayType.MOSAIC) {
			layout.setActiveItem(getItem(0));
		} else if (typeP == DisplayType.LIST) {
			layout.setActiveItem(getItem(1));
		} else if (typeP == DisplayType.DETAIL) {
			layout.setActiveItem(getItem(2));
		}
	}

	/**
	 * Set the store that must be used by file panels
	 * 
	 * @param storeP
	 */
	public ArrayList<AbstractFileUI> getFilesUI() {
		ArrayList<AbstractFileUI> result = new ArrayList<AbstractFileUI>();
		for (int iLoop = 0; iLoop < getItemCount(); iLoop++) {
			Component comp = getItem(iLoop);
			if (comp instanceof AbstractFileUI) {
				result.add((AbstractFileUI) comp);
			}
		}
		return result;
	}

}
