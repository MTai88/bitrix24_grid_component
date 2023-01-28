BX.MyElementList = (function () {
	const List = function (options) {
		this.gridId = options.gridId;
	};

	List.prototype.deleteQueue = function(queueId)
	{
		BX.UI.Dialogs.MessageBox.confirm(
			BX.Loc.getMessage('MTH_CONFIRM_DELETE'),
			async () => {

				const response = await BX.ajax.runComponentAction(`mymodule:myelement.list`, "delete", {
					mode: "class",
					data: {queueId}
				});

				this.reloadGrid();
				return true;
			},
			BX.Loc.getMessage('MTH_BTN_DELETE')
		);
	}

	List.prototype.reloadGrid = function ()
	{
		if (this.gridId)
		{
			const grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
			if (grid)
			{
				grid.reload();
			}
		}
	};

	return List;
})();
