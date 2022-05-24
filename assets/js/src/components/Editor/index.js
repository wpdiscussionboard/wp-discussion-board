import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

const { render, useState } = wp.element;

const Editor = () => {
	return (
		<ReactQuill theme="snow" />
	);
}

export default Editor;
