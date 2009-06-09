package org.dreamsoft.fileexplorer.client.dialog.ui;

import com.extjs.gxt.ui.client.util.Margins;
import com.extjs.gxt.ui.client.util.Padding;
import com.extjs.gxt.ui.client.widget.LayoutContainer;
import com.extjs.gxt.ui.client.widget.layout.VBoxLayout;
import com.extjs.gxt.ui.client.widget.layout.VBoxLayoutData;
import com.extjs.gxt.ui.client.widget.layout.VBoxLayout.VBoxLayoutAlign;

public class ActionPanel extends LayoutContainer {
	public ActionPanel() {
		initUI();
	}

	protected void initUI() {
		VBoxLayout layout = new VBoxLayout();  
        layout.setPadding(new Padding(0));  
        layout.setVBoxLayoutAlign(VBoxLayoutAlign.STRETCH);  
        setLayout(layout);  
  
        add(new AddressToolBar(), new VBoxLayoutData(new Margins(0, 0, 0, 0)));  
        add(new ActionToolBar(), new VBoxLayoutData(new Margins(0, 0, 0, 0)));  

	}
}
