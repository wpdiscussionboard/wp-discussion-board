const TopicActions = () => {
	
	return (
		<div id="topic-actions">
			<a href="#" onClick={openModal}>Edit</a>
			<ReactModal
				isOpen={this.state.showModal}
				contentLabel="Inline Styles Modal Example"
				style={{
					overlay: {
						backgroundColor: 'papayawhip'
					},
					content: {
						color: 'lightsteelblue'
					}
				}}
			>
				<p>Modal text!</p>
				<button onClick={closeModal}>Cancel</button>
				<button onClick={saveEdit}>Save</button>
			</ReactModal>
		</div>
	);
}

export default TopicActions;
